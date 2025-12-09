import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.boot.test.context.SpringBootTest;
import org.springframework.transaction.annotation.Transactional;
import org.junit.jupiter.api.Test;

import static org.junit.jupiter.api.Assertions.*;

@SpringBootTest
@Transactional
public class UserIntegrationTest {

    @Autowired
    private UserService userService;

    @Test
    public void testUserRegistrationAndDuplicateCheck() {

        // 1. Új user regisztráció
        User newUser = new User("testuser", "test@example.com", "pass123");
        User savedUser = userService.registerUser(newUser);

        // 2. Mentés sikeres?
        assertNotNull(savedUser.getId(), "A user ID-nek nem nullnak kell lennie");

        // 3. Dupla regisztráció hibát kell dobjon
        Exception exception = assertThrows(
                RuntimeException.class,
                () -> userService.registerUser(
                        new User("testuser", "other@example.com", "12345")
                )
        );

        assertTrue(exception.getMessage().contains("already exists"));
    }
}
