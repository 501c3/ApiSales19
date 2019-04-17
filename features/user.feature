Feature:
  In order to register for a competition using a web interface or app
  As a parent, teacher or dancesport competitor
  I want to register for a competition

  Scenario: I send new contact information receive authorization/jwt token.
    Given the request body is:
      """
      {
        "name": {"title":"","first":"New","middle":"","last":"User","suffix":""},
        "email": "user@email.com",
        "phone": "(999) 999-9999",
        "mobile": "(678) 999-9999",
        "address": {"country":"USA",
                    "organization": "",
                    "street": "123 Street Address",
                    "city": "City",
                    "state": "GA",
                    "postal": "00000"}
       }
      """
    When I request "/api/sales/register" with method "POST"
    Then the response code is "201"
    And the response status line is "Created"
    And the "Authorization" response header contains "Bearer \w*"
    And I have valid jwt


    Scenario: I send redundant registration information and am sent temp password and redirected.
      Given I am registered as:
      """
      {
        "name": {"title":"","first":"New","middle":"","last":"User","suffix":""},
        "email": "user@email.com",
        "phone": "(999) 999-9999",
        "mobile": "(678) 999-9999",
        "address": {"country":"USA",
                    "organization": "",
                    "street": "123 Street Address",
                    "city": "City",
                    "state": "GA",
                    "postal": "00000"}
       }
      """
      And the request body is:
      """
      {
        "name": {"title":"","first":"New","middle":"","last":"User","suffix":""},
        "email": "user@email.com",
        "phone": "(999) 999-9999",
        "mobile": "(678) 999-9999",
        "address": {"country":"USA",
                    "organization": "",
                    "street": "123 Street Address",
                    "city": "City",
                    "state": "GA",
                    "postal": "00000"}
       }
      """
      When I request "/api/sales/register" with method "POST"
      Then the response code is "308"
      And the response status line is "Permanent Redirect"
      And the response body contains JSON:
      """
       {
         "message": "Redundant contact. Check email for security code.",
         "route": "/api/sales/login"
       }
      """
      And encrypted "password" is saved
      And I receive email with "password"


    Scenario: I login with valid credentials
      Given I am registered as:
      """
      {
        "name": {"title":"","first":"New","middle":"","last":"User","suffix":""},
        "email": "user@email.com",
        "phone": "(999) 999-9999",
        "mobile": "(678) 999-9999",
        "address": {"country":"USA",
                    "organization": "",
                    "street": "123 Street Address",
                    "city": "City",
                    "state": "GA",
                    "postal": "00000"}
       }
      """
      And a temporary password "$argon2i$v=19$m=1024,t=2,p=2$UFBidGhxRUlmWks5R3d2Qw$w+hkawfHsYxdmC8ulniRU3f0NSAXdWozDEjPuhEm9bY" is saved
      And the request body contains credentials "user@email.com" and "1234"
      When I request "/api/sales/login" with method "POST"
      Then the response code is "200"
      And saved password is cleared
      And I have valid jwt



  Scenario: I send invalid username to recontact registration
      Given I am registered as:
      """
      {
        "name": {"title":"","first":"New","middle":"","last":"User","suffix":""},
        "email": "user@email.com",
        "phone": "(999) 999-9999",
        "mobile": "(678) 999-9999",
        "address": {"country":"USA",
                    "organization": "",
                    "street": "123 Street Address",
                    "city": "City",
                    "state": "GA",
                    "postal": "00000"}
       }
      """
      And a temporary password "$argon2i$v=19$m=1024,t=2,p=2$UFBidGhxRUlmWks5R3d2Qw$w+hkawfHsYxdmC8ulniRU3f0NSAXdWozDEjPuhEm9bY" is saved
      And the request body contains credentials "baduser@email.com" and "1234"
      When I request "/api/sales/login" with method "POST"
      Then the response code is "401"
      And the response status line is "Unauthorized"