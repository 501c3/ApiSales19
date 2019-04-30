Feature:
  In order to use on-line competition registration
  As an client application
  I must be able to send http rest requests and receive responses with authorization

  Scenario: I send a registration request and receive an authorization with json web token
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
    And the "Authorization" response header contains "Bearer "

  Scenario: I send redundant registration information and am sent temp password and redirected.
    Given a registration for :
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
    And pin "1234" is emailed to "user@email.com"
    And the response body contains JSON:
    """
     {
       "message": "Redundant contact. Check email for security code.",
       "route": "/api/sales/login"
     }
    """

  Scenario: I login with valid credentials
    Given a registration for :
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
    And pin "1234" is emailed to "user@email.com"
    And the request body is:
    """
    {
      "username": "user@email.com",
      "password": "1234"
    }
    """
    When I request "/api/sales/login" with method "POST"
    Then the response code is "200"
    And the "Authorization" response header contains "Bearer"
    And pin is cleared for "user@email.com"


  Scenario: I send invalid username to recontact registration
    Given a registration for :
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
    And pin "1234" is emailed to "user@email.com"
    And the request body is:
    """
    {
      "username": "user@email.com",
      "password": "4321"
    }
    """
    When I request "/api/sales/login" with method "POST"
    Then the response code is "401"
    And the response status line is "Unauthorized"