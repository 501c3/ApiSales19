Feature:
  In order to add participants to my registration
  After I have registered as a parent, teacher or competitor
  I want to enroll actual participants with their age, proficiency by styles and names

  Background:
    Given the channel "georgia-dancesport" exists
    And I am registered as "user@email.com"
    And JWT is recently generated
    And "user@email.com" is linked to a "competition" workarea for "georgia-dancesport"
    And the "Authorization" request header contains "Bearer "

  Scenario: I add participant
    Given the participant request body is:
    """
    {
      "name": {"first": "New", "last": "Participant"},
               "sex": "Male",
               "typeA": "Teacher",
               "typeB": "Professional",
               "age": 0,
               "proficiency": { "Latin": "Professional",
                                "Standard": "Professional",
                                "Rhythm": "Professional",
                                "Smooth": "Professional"}
    }
    """
    When I add participant request "/api/sales/participant" with method "POST"
    Then participant response code is "201"
    And participant response status line is "Created"
    And participant response JSON has field "id"
    And participant response JSON has field "tag"
    And participant response JSON has field "name"
    And participant response JSON tag field is "participant"
    And participant Authorization header JWT indicates user "user@email.com"


  Scenario: I request list of participants
    Given I have entered multiple participants
    When I request "/api/sales/participant" for participants
    Then I receive participant list
    And each participant list entry has field "name"
    And each participant list entry has field "id"
    And each participant list entry has field "tag"

  Scenario: I request information for one participant
    Given I have a participant with id of "3"
    When I request "/api/sales/participant/3" for info
    Then info has field "name"
    And info has field "years"
    And info has multiple fields for "proficiency"
    And info has field "typeA" of "Teacher" or "Student"
    And info has field "typeB" of "Professional" or "Amateur"
