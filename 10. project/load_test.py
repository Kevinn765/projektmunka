from locust import HttpUser, task, between

class LoadTestUser(HttpUser):
    wait_time = between(1, 2)  # user think-time

    @task
    def test_homepage(self):
        self.client.get("/")

    @task
    def test_login(self):
        self.client.post("/api/login", json={
            "email": "bturbo@gmail.com",
            "password": "valami"
        })
