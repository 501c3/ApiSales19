Feature:
  In order to register entries
  After participants are entered and organized into couples or solo.
  I must select the events they may enter.

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
    And I have added teams:
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

  Scenario: I POST entries for a team
    Given the request body is:
    """
      {
        "team-id": 13,
        "entry-ids": [585,589,1288]
      }
    """
    When I request "/api/sales/entries" with method "POST"
    Then the response code is "201"
    And the response status line is "Created"
    And the "Authorization" response header contains "Bearer "
    And entry form "19" has entry-id "585"
    And entry form "19" has entry-id "589"
    And entry form "19" has entry-id "1288"


  Scenario: I PUT (revise) entries for a team
    Given I have posted entries:
    |team-id|event-id0|event-id1|event-id2|
    |13     |585      |589      |1288     |
    And the request body is:
    """
      {
        "team-id-entries": 19,
        "entry-ids": [589,1288,1289]
      }
    """
    When I request "/api/sales/entries" with method "PUT"
    Then the response code is "202"
    And the response status line is "Accepted"
    And the "Authorization" response header contains "Bearer "
    And entry form "19" has entry-id "589"
    And entry form "19" has entry-id "1288"
    And entry form "19" has entry-id "1289"
    And entry form "19" has no entry-id "585"

  Scenario: I DELETE all entries for teams
    Given I have posted entries:
    |team-id|event-id0|event-id1|event-id2|
    |13     |585      |589      |1288     |
    And entry form "19" has entry-id "585"
    And entry form "19" has entry-id "589"
    And entry form "19" has entry-id "1288"
    And the request body is:
    """
    {
      "team-id-entries": 19
    }
    """
    When I request "/api/sales/entries" with method "DELETE"
    Then the response code is "202"
    And the response status line is "Accepted"
    And the "Authorization" response header contains "Bearer "
    And entry form "19" does not exist
