from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
import time

print("Running test: Sign In Buyer")

driver = webdriver.Chrome()
driver.get("http://localhost/Luxora/loginPage.php")
wait = WebDriverWait(driver, 10)

driver.execute_script("""
    document.getElementById('signIn').style.display = 'block';
    document.getElementById('signUp').style.display = 'none';
    document.getElementById('signUpSeller').style.display = 'none';
    document.getElementById('signInSeller').style.display = 'none';
""")

try:
    email_input = wait.until(EC.visibility_of_element_located((By.ID, "email")))
    password_input = wait.until(EC.visibility_of_element_located((By.ID, "password")))

    email_input.clear()
    email_input.send_keys("kiculbuyer@example.com")
    password_input.clear()
    password_input.send_keys("Password123")

    driver.find_element(By.NAME, "signIn").click()

    time.sleep(2)
    if "homeCustomer" in driver.current_url:
        print("Sign In Buyer: Passed")
    else:
        print("Sign In Buyer: Failed - Wrong redirect or login failed")

except Exception as e:
    print("Sign In Buyer: Failed -", str(e))


driver.quit()
