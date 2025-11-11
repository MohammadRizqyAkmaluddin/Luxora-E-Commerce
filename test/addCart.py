from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.common.keys import Keys
import time

class TestAddToCart:
    def setup_method(self):
        self.driver = webdriver.Chrome()
        self.driver.get("http://localhost/Luxora/loginPage.php")

    def teardown_method(self):
        self.driver.quit()

    def test_add_to_cart(self):
        username_input = self.driver.find_element(By.NAME, "username")
        password_input = self.driver.find_element(By.NAME, "password")
        login_button = self.driver.find_element(By.XPATH, "//button[@type='submit']")

        username_input.send_keys("kiculbuyer@example.com")
        password_input.send_keys("Password123")
        login_button.click()

        time.sleep(2) 
        self.driver.get("http://localhost/Luxora/products.php?type=all")

        add_to_cart_button = self.driver.find_element(By.XPATH, "//button[@data-product-id='PRO001']") 
        add_to_cart_button.click()

        time.sleep(2)
        cart_icon = self.driver.find_element(By.XPATH, "//a[@href='http://localhost/Luxora/cartManagement.php']")
        cart_icon.click()

        time.sleep(2)  
        product_in_cart = self.driver.find_element(By.XPATH, "//h1[contains(text(), 'Rasant Rib Long Sleeve Dress')]")
        assert product_in_cart.is_displayed(), "Product was not added to the cart."

        quantity_element = self.driver.find_element(By.XPATH, "//p[@class='2']")
        assert quantity_element.text == "1", "Quantity is not correct."

        price_element = self.driver.find_element(By.XPATH, "//div[@class='85']")
        assert price_element.text == "$85.00", "Price is not correct."

if __name__ == "__main__":
    test = TestAddToCart()
    test.setup_method()
    try:
        test.test_add_to_cart()
    finally:
        test.teardown_method()
