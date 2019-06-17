Feature:
  In order to add participants to my registration
  After I have registered as a parent, teacher or competitor
  I want to enroll actual participants with their age, proficiency by styles and names

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


  Scenario Outline: I add participants
    Given the participant request body is:
    """
    {
      "name": {"first": "<first>", "last": "<last>"},
      "sex": "<sex>",
      "status": "<status>",
      "type": "<type>",
      "years": "<years>",
      "model": "<models>",
      "proficiency": {"Latin": "<latin>",
                      "Standard": "<standard>",
                      "Rhythm": "<rhythm>",
                      "Smooth": "<smooth>"}
    }
    """
    When I request "/api/sales/participant" with method "POST"
    Then the response code is "201"
    And the response status line is "Created"
    And the response body contains JSON:
    """
    {
      "name": {"first": "<first>", "last": "<last>"},
      "tag": "participant"
    }
    """
    And the response body contains "id"
    And the "Authorization" response header contains "Bearer "
    Examples:
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


  Scenario: I request list of participants
    Given I have entered multiple participants:
    |first      |last       |sex|status |type   |years|latin              |standard           |rhythm             |smooth             |models|
    |PreBronze-F|Baby-5     |F  |Student|Amateur|5    |Pre Bronze         |Pre Bronze         |Pre Bronze         |Pre Bronze         |ISTD Medal Exams-2019,Georgia DanceSport Amateur-2019|
    |PreBronze-M|Baby-5     |M  |Student|Amateur|5    |Pre Bronze         |Pre Bronze         |Pre Bronze         |Pre Bronze         |ISTD Medal Exams-2019,Georgia DanceSport Amateur-2019|
    |Silver-F   |Junior1-13 |F  |Student|Amateur|13   |Intermediate Silver|Intermediate Silver|Intermediate Silver|Intermediate Silver|ISTD Medal Exams-2019,Georgia DanceSport Amateur-2019,Georgia DanceSport ProAm-2019|
    |Silver-M   |Junior1-13 |M  |Student|Amateur|13   |Intermediate Silver|Intermediate Silver|Intermediate Silver|Intermediate Silver|ISTD Medal Exams-2019,Georgia DanceSport Amateur-2019,Georgia DanceSport ProAm-2019|
    |Gold-F     |Youth-16   |F  |Student|Amateur|16   |Full Gold          |Full Gold          |Full Gold          |Full Gold          |ISTD Medal Exams-2019,Georgia DanceSport Amateur-2019,Georgia DanceSport ProAm-2019|
    |Gold-M     |Youth-16   |M  |Student|Amateur|16   |Full Gold          |Full Gold          |Full Gold          |Full Gold          |ISTD Medal Exams-2019,Georgia DanceSport Amateur-2019,Georgia DanceSport ProAm-2019|
    |Novice-F   |Adult-30   |F  |Student|Amateur|30   |Novice             |Novice             |Novice             |Novice             |Georgia DanceSport Amateur-2019|
    |Novice-M   |Adult-30   |M  |Student|Amateur|30   |Novice             |Novice             |Novice             |Novice             |Georgia DanceSport Amateur-2019|
    |Prechamp-F |Senior1-35 |F  |Student|Amateur|35   |Pre Championship   |Pre Championship   |Pre Championship   |Pre Championship   |Georgia DanceSport Amateur-2019|
    |Prechamp-M |Senior1-35 |M  |Student|Amateur|35   |Pre Championship   |Pre Championship   |Pre Championship   |Pre Championship   |Georgia DanceSport Amateur-2019|
    |Pro-F      |Adult-0    |F  |Teacher|Professional|0|Professional      |Professional       |Professional       |Professional       |Georgia DanceSport ProAm-2019|
    |Pro-M      |Adult-0    |M  |Teacher|Professional|0|Professional      |Professional       |Professional       |Professional       |Georgia DanceSport ProAm-2019|
    When I request "/api/sales/participant" with method "GET"
    Then the response code is 202
    And the response status line is Accepted
    And the response body is a JSON array of length 12
    And the Authorization response header contains Bearer 
    And response list entry has field name
    And response list entry has field id
    And response list entry has field tag

  Scenario: I request information for 3rd participant
    Given I have entered multiple participants:
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
    When I request "/api/sales/participant/3" with method "GET"
    Then the response code is "202"
    And the response status line is "Accepted"
    And the response body is a JSON array of length "9"
    And the "Authorization" response header contains "Bearer "
    And the response body contains JSON:
    """
    {
      "name" : {"first": "Silver-F", "last": "Junior1-13"},
      "sex" : "F",
      "status": "Student",
      "type": "Amateur",
      "model": ["ISTD Medal Exams-2019","Georgia DanceSport Amateur-2019","Georgia DanceSport ProAm-2019"],
      "proficiency": {"Latin": "Intermediate Silver",
                      "Standard": "Intermediate Silver",
                      "Rhythm": "Intermediate Silver",
                      "Smooth": "Intermediate Silver"}
    }
    """
    And the response body contains field for "id"


  Scenario: I request information for non existent participant
    Given I have entered multiple participants:
      |first      |last      |sex|status |type   |years|latin              |standard           |rhythm             |smooth             |models|
      |PreBronze-F|Baby-5    |F  |Student|Amateur|5    |Pre Bronze         |Pre Bronze         |Pre Bronze         |Pre Bronze         |ISTD Medal Exams-2019,Georgia DanceSport Amateur-2019|
      |PreBronze-M|Baby-5    |M  |Student|Amateur|5    |Pre Bronze         |Pre Bronze         |Pre Bronze         |Pre Bronze         |ISTD Medal Exams-2019,Georgia DanceSport Amateur-2019|
      |Silver-F   |Junior1-13|F  |Student|Amateur|13   |Intermediate Silver|Intermediate Silver|Intermediate Silver|Intermediate Silver|ISTD Medal Exams-2019,Georgia DanceSport Amateur-2019,Georgia DanceSport ProAm-2019|
      |Silver-M   |Junior1-13|M  |Student|Amateur|13   |Intermediate Silver|Intermediate Silver|Intermediate Silver|Intermediate Silver|ISTD Medal Exams-2019,Georgia DanceSport Amateur-2019,Georgia DanceSport ProAm-2019|
      |Gold-F     |Youth-16  |F  |Student|Amateur|16   |Full Gold          |Full Gold          |Full Gold          |Full Gold          |ISTD Medal Exams-2019,Georgia DanceSport Amateur-2019,Georgia DanceSport ProAm-2019|
      |Gold-M     |Youth-16  |M  |Student|Amateur|16   |Full Gold          |Full Gold          |Full Gold          |Full Gold          |ISTD Medal Exams-2019,Georgia DanceSport Amateur-2019,Georgia DanceSport ProAm-2019|
      |Novice-F   |Adult-30  |F  |Student|Amateur|30   |Novice             |Novice             |Novice             |Novice             |Georgia DanceSport Amateur-2019|
      |Novice-M   |Adult-30  |M  |Student|Amateur|30   |Novice             |Novice             |Novice             |Novice             |Georgia DanceSport Amateur-2019|
      |Prechamp-F |Senior1-35|F  |Student|Amateur|35   |Pre Championship   |Pre Championship   |Pre Championship   |Pre Championship   |Georgia DanceSport Amateur-2019|
      |Prechamp-M |Senior1-35|M  |Student|Amateur|35   |Pre Championship   |Pre Championship   |Pre Championship   |Pre Championship   |Georgia DanceSport Amateur-2019|
      |Pro-F      |Adult-0   |F  |Teacher|Professional|0|Professional      |Professional       |Professional       |Professional       |Georgia DanceSport ProAm-2019|
      |Pro-M      |Adult-0   |M  |Teacher|Professional|0|Professional      |Professional       |Professional       |Professional       |Georgia DanceSport ProAm-2019|
    When I request "/api/sales/participant/13" with method "GET"
    Then the response code is "404"
    And the response status line is "Not Found"
    And the "Authorization" response header contains "Bearer "
    And the response body contains JSON:
    """
    {
      "id": 13,
      "status": "fail",
      "message": "Participant not found."
    }
    """
