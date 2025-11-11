from selenium import webdriver
from selenium.webdriver.common.by import By
import time

print("Running test: Sign In Seller")

driver = webdriver.Chrome()
driver.get("http://localhost/Luxora/loginPage.php")

driver.execute_script("document.getElementById('signInSeller').style.display = 'block';")

time.sleep(1)
driver.find_element(By.NAME, "sEmail").send_keys("toko@example.com")
driver.find_element(By.NAME, "sPassword").send_keys("Password123")
driver.find_element(By.NAME, "signInSeller").click()

time.sleep(2)
if "homeStore" in driver.current_url:
    print("Sign In Seller: Passed")
else:
    print("Sign In Seller: Failed")

driver.quit()
