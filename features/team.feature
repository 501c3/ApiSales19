Feature:
  In order to enter competitors into events
  After I have entered my competitors/participants
  I must organize them into couples or solo and compute age and proficiency classification as a team.

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
    And I have entered multiple participants:
      |first      |last       |sex|status |type   |years|latin                   |standard              |rhythm              |smooth             |models|
      |PreBronze-F|Baby-4     |F  |Student|Amateur|4    |Pre Bronze              |Pre Bronze            |Pre Bronze          |Pre Bronze         |ISTD Medal Exams-2019,Georgia DanceSport Amateur-2019|
      |PreBronze-M|Baby-4     |M  |Student|Amateur|4    |Pre Bronze              |Pre Bronze            |Pre Bronze          |Pre Bronze         |ISTD Medal Exams-2019,Georgia DanceSport Amateur-2019|
      |Silver-F   |Junior1-13 |F  |Student|Amateur|13   |Intermediate Silver     |Intermediate Silver   |Intermediate Silver |Intermediate Silver|ISTD Medal Exams-2019,Georgia DanceSport Amateur-2019,Georgia DanceSport ProAm-2019|
      |Silver-M   |Junior1-13 |M  |Student|Amateur|13   |Intermediate Silver     |Intermediate Silver   |Intermediate Silver |Intermediate Silver|ISTD Medal Exams-2019,Georgia DanceSport Amateur-2019,Georgia DanceSport ProAm-2019|
      |Gold-F     |Youth-16   |F  |Student|Amateur|16   |Full Gold               |Full Gold             |Full Gold           |Full Gold          |Georgia DanceSport Amateur-2019,Georgia DanceSport ProAm-2019|
      |Gold-M     |Youth-16   |M  |Student|Amateur|16   |Full Gold               |Full Gold             |Full Gold           |Full Gold          |Georgia DanceSport Amateur-2019,Georgia DanceSport ProAm-2019|
      |Novice-F   |Adult-30   |F  |Student|Amateur|30   |Novice                  |Novice                |Novice              |Novice             |Georgia DanceSport Amateur-2019|
      |Novice-M   |Adult-30   |M  |Student|Amateur|30   |Novice                  |Novice                |Novice              |Novice             |Georgia DanceSport Amateur-2019|
      |Prechamp-F |Senior1-35 |F  |Student|Amateur|35   |Pre Championship        |Pre Championship      |Pre Championship    |Pre Championship   |Georgia DanceSport Amateur-2019|
      |Prechamp-M |Senior1-35 |M  |Student|Amateur|35   |Pre Championship        |Pre Championship      |Pre Championship    |Pre Championship   |Georgia DanceSport Amateur-2019|
      |Pro-F      |Adult-0    |F  |Teacher|Professional|0|Professional           |Professional          |Professional        |Professional       |Georgia DanceSport ProAm-2019|
      |Pro-M      |Adult-0    |M  |Teacher|Professional|0|Professional           |Professional          |Professional        |Professional       |Georgia DanceSport ProAm-2019|
    And participants have ids:
      |first      |last      |id|
      |PreBronze-F|Baby-4    |1 |
      |PreBronze-M|Baby-4    |2 |
      |Silver-F   |Junior1-13|3 |
      |Silver-M   |Junior1-13|4 |
      |Gold-F     |Youth-16  |5 |
      |Gold-M     |Youth-16  |6 |
      |Novice-F   |Adult-30  |7 |
      |Novice-M   |Adult-30  |8 |
      |Prechamp-F |Senior1-35|9 |
      |Prechamp-M |Senior1-35|10|
      |Pro-F      |Adult-0   |11|
      |Pro-M      |Adult-0   |12|

  Scenario: I submit a team request and receive event selections.
    Given the request body is:
    """
    {
        "team-request" : [3,4]
     }
    """
    When I request "/api/sales/team" with method "POST"
    Then the response code is "201"
    And the response status line is "Created"
    And the response body contains field for "team"
    And the response body contains field for "selections"
    And the response body contains field for "id"
    And available "proficiency" in "Georgia DanceSport Amateur-2019" for "Silver"
    And available "proficiency" in "Georgia DanceSport Amateur-2019" for "Gold"
    And available "age" in "Georgia DanceSport Amateur-2019" for "Junior 1"
    And available "status" in "Georgia DanceSport Amateur-2019" for "Student-Student"
    And available "type" in "Georgia DanceSport Amateur-2019" for "Amateur-Amateur"
    And not available "proficiency" in "Georgia DanceSport Amateur-2019" for "Bronze"
    And not available "proficiency" in "Georgia DanceSport Amateur-2019" for "Championship"
    And not available "proficiency" in "Georgia DanceSport Amateur-2019" for "Pre Championship"
    And not available "age" in "Georgia DanceSport Amateur-2019" for "Baby"
    And not available "age" in "Georgia DanceSport Amateur-2019" for "Adult"
    And not available "status" in "Georgia DanceSport Amateur-2019" for "Teacher"
    And not available "type" in "Georgia DanceSport Amateur-2019" for "Professional"

  Scenario: I have entered multiple teams and delete a team
    Given I have added teams:
    |id-left|id-right|
    |1      |2       |
    |3      |4       |
    |5      |6       |
    |7      |8       |
    |9      |10      |
    |11     |12      |
    And teams have ids:
    |id-left|id-right|id|name                       |
    |1      |2       |13|Baby-4 & Baby-4            |
    |3      |4       |14|Junior1-13 & Junior1-13    |
    |5      |6       |15|Youth-16 & Youth-16        |
    |7      |8       |16|Adult-30 & Adult-30        |
    |9      |10      |17|Senior1-35 & Senior1-35    |
    |11     |12      |18|Adult-0 & Adult-0          |
    And the request body is:
    """
    {
      "team-delete":[13,14]
    }
    """
    When I request "/api/sales/team" with method "DELETE"
    Then the response code is "200"
    And the response body contains JSON Array:
    """
    [
      {"id": 13, "status": "success", "tag": "team", "message": "Removed: Baby-4 & Baby-4"},
      {"id": 14, "status": "success", "tag": "team", "message": "Removed: Junior1-13 & Junior1-13"}
    ]
    """

    Scenario: I request a list of teams with IDs
      Given I have added teams:
        |id-left|id-right|
        |1      |2       |
        |3      |4       |
        |5      |6       |
        |7      |8       |
        |9      |10      |
        |11     |12      |
      And teams have ids:
        |id-left|id-right|id|name                       |
        |1      |2       |13|Baby-4 & Baby-4            |
        |3      |4       |14|Junior1-13 & Junior1-13    |
        |5      |6       |15|Youth-16 & Youth-16        |
        |7      |8       |16|Adult-30 & Adult-30        |
        |9      |10      |17|Senior1-35 & Senior1-35    |
        |11     |12      |18|Adult-0 & Adult-0          |
      When I request "/api/sales/team" with method "GET"
      Then the response code is "202"
      And the response status line is "Accepted"
      And the response body contains JSON Array:
      """
      [
        {"id": 13, "name": "Baby-4 & Baby-4", "tag": "team"},
        {"id": 14, "name": "Junior1-13 & Junior1-13", "tag": "team"},
        {"id": 15, "name": "Youth-16 & Youth-16", "tag": "team"},
        {"id": 16, "name": "Adult-30 & Adult-30", "tag": "team"},
        {"id": 17, "name": "Senior1-35 & Senior1-35", "tag": "team"},
        {"id": 18, "name": "Adult-0 & Adult-0", "tag": "team"}
      ]
      """

  Scenario: I remove a participant and subsequently the team.
    Given I have added teams:
      |id-left|id-right|
      |1      |2       |
      |3      |4       |
      |5      |6       |
      |7      |8       |
      |9      |10      |
      |11     |12      |
    And teams have ids:
      |id-left|id-right|id|name                       |
      |1      |2       |13|Baby-4 & Baby-4            |
      |3      |4       |14|Junior1-13 & Junior1-13    |
      |5      |6       |15|Youth-16 & Youth-16        |
      |7      |8       |16|Adult-30 & Adult-30        |
      |9      |10      |17|Senior1-35 & Senior1-35    |
      |11     |12      |18|Adult-0 & Adult-0          |
    And the request body is:
      """
      {
        "participant-delete":[1,11]
      }
      """
    When I request "/api/sales/participant" with method "DELETE"
    Then the response code is "202"
    And the response status line is "Accepted"
    And the response body contains JSON Array:
      """
      [
        {"id": 13, "status": "success", "tag": "team", "message": "Removed: Baby-4 & Baby-4"},
        {"id": 1,  "status": "success", "tag": "participant", "message": "Removed: Baby-4, PreBronze-F"},
        {"id": 18, "status": "success", "tag": "team", "message": "Removed: Adult-0 & Adult-0"},
        {"id": 11, "status": "success", "tag": "participant", "message": "Removed: Adult-0, Pro-F"}
      ]
      """

  Scenario: I add a team redundantly and receive an error message
    Given I have added teams:
      |id-left|id-right|
      |1      |2       |
      |3      |4       |
      |5      |6       |
      |7      |8       |
      |9      |10      |
      |11     |12      |
    And the request body is:
      """
      {
          "team-request" : [1,2]
      }
      """
    When I request "/api/sales/team" with method "POST"
    Then the response code is "403"
    And the response status line is "Forbidden"
