from flask import Flask, request, jsonify, send_file

from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.firefox.service import Service
from selenium.webdriver.support import expected_conditions as EC

import time
import os
import sys
from bs4 import BeautifulSoup

sys.path.append(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
from config import config


def init_driver():
    options = webdriver.FirefoxOptions()
    options.set_preference("marionette.port", 2828)
    options.add_argument("--headless")
    service = Service(config.GECKODRIVER_PATH)
    driver = webdriver.Firefox(service=service, options=options)

    return driver
def get_table_data(x_path, driver):

    # Load reservation table
    WebDriverWait(driver, 10).until(
        EC.presence_of_element_located((By.XPATH, x_path))
    )

    # Extract table rows
    table_element = driver.find_element(By.XPATH, x_path)
    table_html = table_element.get_attribute('outerHTML')
    soup = BeautifulSoup(table_html, 'html.parser')
    rows = soup.find_all('tr')

    header = []
    data = []

    for row_num, row in enumerate(rows):
        columns = row.find_all(['td', 'th'])
        if len(columns) > 3:
            row_data = [
                columns[1].get_text(strip=True), # Day
                columns[3].get_text(strip=True), # Food
            ]
            
            if row_num == 0:
                header = row_data
            else:
                data.append(row_data)

    formatted_text = ""

    # forming the text
    for row in data:
        day, meals = row
        formatted_text += f"{day}:\n"
        
        if meals == '-':
            formatted_text += "🍌\n\n"
        else:
            meal_items = [item.strip() + " ریال" for item in meals.split("ریال") if item.strip()]
            formatted_text += "\n".join(meal_items) + "\n\n"

    formatted_text = formatted_text.replace("@", "<b>[رستوران]</b> ")
    return formatted_text

captcha_dir = config.CAPTCHA_DIR

app = Flask(__name__)
sessions = {}

@app.route('/get_captcha', methods=['GET'])
def capture_captcha():
    user_id = request.args.get('user_id') 
    driver = sessions.get(str(user_id))

    if driver:
        driver.quit()
    driver = init_driver()
    sessions[user_id] = driver

    try:
        driver.get(config.WEBSITE_URL)

        captcha_element = WebDriverWait(driver, 10).until(
        EC.presence_of_element_located((By.ID, "Img1"))
        )

        captcha_filename = f"captcha_{user_id}.png"
        captcha_path = os.path.join(captcha_dir, captcha_filename)
        captcha_element.screenshot(captcha_path)

        return send_file(captcha_path, mimetype='image/png')

    except Exception as e:
        print(f"Error in capture_captcha: {e}\nConnection closed.")


@app.route('/login', methods=['POST'])
def login():
    data = request.json
    user_id = data['user_id']

    usrName = config.USERNAME
    usrPass = config.PASSWORD
    captcha_input = data['captcha']
    captcha_input = str(captcha_input)

    driver = sessions.get(str(user_id))

    driver.find_element(By.ID, "txtUsernamePlain").send_keys(usrName)
    driver.find_element(By.ID, "txtPasswordPlain").send_keys(usrPass)
    driver.find_element(By.ID, "txtCaptcha").send_keys(captcha_input)
    
    driver.find_element(By.ID, "btnEncript").click()
    time.sleep(5) 

    if "پروفایل" in driver.page_source:
        print("Loged in")
        return jsonify({"status": "success"})
    else:
        print("Can't Login\nstayed on:", driver.title)
        return jsonify({"status": "failed"})


@app.route('/fetch_data', methods=['GET'])
def fetch_data():
    data = request.json
    user_id = data['user_id']
    week_choice = data['week']  # "this" or "next"

    driver = sessions.get(str(user_id))
    if not driver:
        return jsonify({"error": "Session not found"}), 404

    # Navigate to reservation page
    driver.get(config.WEBSITE_URL + "Reservation/Reservation.aspx")

    if week_choice == "thisweek":
        breakfast_data = get_table_data('//*[@id="cphMain_grdReservationBreakfast"]', driver)
        lunch_data = get_table_data('//*[@id="cphMain_grdReservationLunch"]', driver)
        dinner_data = get_table_data('//*[@id="cphMain_grdReservationDinner"]', driver)
    
    elif week_choice == "nextweek":
        nxt_week = driver.find_element(By.ID, "cphMain_imgbtnNextWeek")
        nxt_week.click()
        time.sleep(3)

        breakfast_data = get_table_data('//*[@id="cphMain_grdReservationBreakfast"]', driver)
        lunch_data = get_table_data('//*[@id="cphMain_grdReservationLunch"]', driver)
        dinner_data = get_table_data('//*[@id="cphMain_grdReservationDinner"]', driver)
    
    driver.quit()
    
    return jsonify({
        "breakfast": breakfast_data,
        "lunch": lunch_data,
        "dinner": dinner_data
    })


if __name__ == "__main__":
    app.run(host="0.0.0.0", port=5000)
