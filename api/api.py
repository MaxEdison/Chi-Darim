sys.path.append(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
from config import config

from flask import Flask, request, jsonify, send_file

from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.firefox.service import Service
from selenium.webdriver.support import expected_conditions as EC


def init_driver():
    options = webdriver.FirefoxOptions()
    options.set_preference("marionette.port", 2828)
    options.add_argument("--headless")
    service = Service(config.GECKODRIVER_PATH)
    driver = webdriver.Firefox(service=service, options=options)

    return driver
def get_table_data(x_path, driver):

    # Wait for the reservation table to load
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
        driver.get()

        captcha_element = WebDriverWait(driver, 10).until(
        EC.presence_of_element_located((By.ID, "Img1"))
        )

        captcha_filename = f"captcha_{user_id}.png"
        captcha_path = os.path.join(captcha_dir, captcha_filename)
        captcha_element.screenshot(captcha_path)
        
        return send_file(captcha_path, mimetype='image/png')

    except Exception as e:
        print(f"Error in capture_captcha: {e}\nConnection closed.")
