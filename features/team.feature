Feature:
  In order to enter competitors into events
  After I have entered my competitors
  I must organize them into couples or solo and compute age and proficiency classification

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
      |PreBronze-F|Baby-5     |F  |Student|Amateur|5    |Pre Bronze              |Pre Bronze            |Pre Bronze          |Pre Bronze         |ISTD Medal Exams-2019,Georgia DanceSport Amateur-2019|
      |PreBronze-M|Baby-5     |M  |Student|Amateur|5    |Pre Bronze              |Pre Bronze            |Pre Bronze          |Pre Bronze         |ISTD Medal Exams-2019,Georgia DanceSport Amateur-2019|
      |Silver-F   |Junior1-13 |F  |Student|Amateur|13   |Intermediate Silver     |Intermediate Silver   |Intermediate Silver |Intermediate Silver|ISTD Medal Exams-2019,Georgia DanceSport Amateur-2019,Georgia DanceSport ProAm-2019|
      |Silver-M   |Junior1-13 |M  |Student|Amateur|13   |Intermediate Silver     |Intermediate Silver   |Intermediate Silver |Intermediate Silver|ISTD Medal Exams-2019,Georgia DanceSport Amateur-2019,Georgia DanceSport ProAm-2019|
      |Gold-F     |Youth-16   |F  |Student|Amateur|16   |Full Gold               |Full Gold             |Full Gold           |Full Gold          |ISTD Medal Exams-2019,Georgia DanceSport Amateur-2019,Georgia DanceSport ProAm-2019|
      |Gold-M     |Youth-16   |M  |Student|Amateur|16   |Full Gold               |Full Gold             |Full Gold           |Full Gold          |ISTD Medal Exams-2019,Georgia DanceSport Amateur-2019,Georgia DanceSport ProAm-2019|
      |Novice-F   |Adult-30   |F  |Student|Amateur|30   |Novice                  |Novice                |Novice              |Novice             |Georgia DanceSport Amateur-2019|
      |Novice-M   |Adult-30   |M  |Student|Amateur|30   |Novice                  |Novice                |Novice              |Novice             |Georgia DanceSport Amateur-2019|
      |Prechamp-F |Senior1-35 |F  |Student|Amateur|35   |Pre Championship        |Pre Championship      |Pre Championship    |Pre Championship   |Georgia DanceSport Amateur-2019|
      |Prechamp-M |Senior1-35 |M  |Student|Amateur|35   |Pre Championship        |Pre Championship      |Pre Championship    |Pre Championship   |Georgia DanceSport Amateur-2019|
      |Pro-F      |Adult-0    |F  |Teacher|Professional|0|Professional           |Professional          |Professional        |Professional       |Georgia DanceSport ProAm-2019|
      |Pro-M      |Adult-0    |M  |Teacher|Professional|0|Professional           |Professional          |Professional        |Professional       |Georgia DanceSport ProAm-2019|
    And participants have ids:
      |first      |last      |id|
      |PreBronze-F|Baby-5    |1 |
      |PreBronze-M|Baby-5    |2 |
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

  Scenario: I assemble a couple qualifications from my participants partnerships
    Given the request body is:
    """
    {
        "team-request" : [3,4]
     }
    """
    When I request "/api/sales/team" with method "POST"
#    Then the response body for team contains JSON:
#    """
#      {
#        "team": "Silver-M & Gold-F",
#        "model": {"Georgia DanceSport Amateur-2019"}
#        "proficiency: { "Standard": "Gold",
#                        "Latin": "Gold",
#                        "Smooth": "Gold",
#                        "Rhythm": "Gold"}
#
#      }
#    """
#    And the response body for team contains field for "event-selections"
#    And the response body for team contains field for "id"

