sys.path.append(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
from config import config

def init_driver():
    options = webdriver.FirefoxOptions()
    options.set_preference("marionette.port", 2828)
    options.add_argument("--headless")
    service = Service(config.GECKODRIVER_PATH)
    driver = webdriver.Firefox(service=service, options=options)

    return driver
    