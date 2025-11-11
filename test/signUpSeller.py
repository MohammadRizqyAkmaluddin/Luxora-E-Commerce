from selenium import webdriver
from selenium.webdriver.common.by import By
import time

print("Running test: Sign Up Seller")

driver = webdriver.Chrome()
driver.get("http://localhost/Luxora/loginPage.php")

driver.execute_script("document.getElementById('signUpSeller').style.display = 'block';")

time.sleep(1)
driver.find_element(By.NAME, "sName").send_keys("TokoLaura")

driver.find_element(By.NAME, "sPhone").send_keys("082233445566")
driver.find_element(By.NAME, "sAddress").send_keys("Jl. Melati No. 9")
driver.find_element(By.NAME, "sEmail").send_keys("toko@example.com")
driver.find_element(By.NAME, "sPassword").send_keys("Password123")
driver.find_element(By.NAME, "signUpSeller").click()

time.sleep(2)
if "loginPage.php" in driver.current_url:
    print("Sign Up Seller: Passed")
else:
    print("Sign Up Seller: Failed")

driver.quit()
