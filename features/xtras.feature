Feature:
  In order to facilitate non-competition items
  After selecting the event to compete
  I may request spectator tickets and programs

  Background:
    Given A channel is defined:
    """
    {
    "name" :"georgia-dancesport",
    "heading": {
                "name": "Georgia DanceSport Competition & ISTD Medal Exams",
                "venue": "Ballroom Impact",
                "city": "Sandy Springs",
                "state": "GA",
                "date": {"start":"2019-09-21","stop":"2019-09-21"}
                },
     "logo":"dancers-icon.png"
    }

    """
    And a registration for :
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
    And I request "/api/sales/login" with method "POST"
    And the "Authorization" response header contains "Bearer "


  Scenario: I receive a price list
    When I request "/api/sales/inventory" with method "GET"
    Then the response code is "202"
    And the response status line is "Accepted"
    And the response body contains JSON:
    """
    {
      "program": {"qty": 0, "price": 7},
      "spectator-adult": {"qty": 0,"price": 10},
      "spectator-child": {"qty": 0,"price": 7}
    }
    """

  Scenario: I request xtras
    Given the request body is:
    """
      {
      "program": 1,
      "spectator-adult": 2
      }
    """
    When I request "/api/sales/xtras" with method "POST"
    Then the response code is "201"
    And the response status line is "Created"
    And the "Authorization" response header contains "Bearer "
    And the response body contains JSON:
    """
      {"id": 1, "tag": "xtras", "status": "success", "message": "Posted xtras purchase"}
    """