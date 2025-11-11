from selenium import webdriver
from selenium.webdriver.common.by import By
import time

print("Running test: Sign Up Buyer")

driver = webdriver.Chrome()
driver.get("http://localhost/Luxora/loginPage.php")

# Tampilkan form Sign Up Buyer
driver.execute_script("document.getElementById('signUp').style.display = 'block';")

time.sleep(1)
driver.find_element(By.NAME, "fName").send_keys("Kicul")
driver.find_element(By.NAME, "lName").send_keys("Buyer")
driver.find_element(By.NAME, "phoneNum").send_keys("08123456789")
driver.find_element(By.NAME, "address").send_keys("Jl. Bunga No. 1")
driver.find_element(By.NAME, "gender").send_keys("Female")
driver.find_element(By.NAME, "email").send_keys("kiculbuyer@example.com")
driver.find_element(By.NAME, "password").send_keys("Password123")
driver.find_element(By.NAME, "signUp").click()

time.sleep(3)
if "loginPage.php" in driver.current_url:
    print("Sign Up Buyer: Passed")
else:
    print("Sign Up Buyer: Failed")

driver.quit()
