# UserLytics
# Twitter Analytics Utility

## Requirements
  - PHP 5.3+
  - cURL
  - Twitter Developer Account: https://dev.twitter.com
  
## Purpose
- Return "fully-hydrated" user data
  - Follower/following analysis
    - Profile card:
      - full-name
      - user-name
      - description
      - profile-image
  - Data metrics of user
    - Followers number
    - Following number
  - GPS location
  - Account creation date
  - Verified status
  - Tweet/mention impressions

## Use Cases
- See who a users top followers are
- View closest users to a chosen location
- Breakdown a particular users influence "reach" within a network

## Usage
- Utilize Twitter REST API (Users/Lookup) resource
```
    \\Pass Twitter username as an argument to userlytics.php
    php userlytics.php <user_name>
```
- Performs requests in batches of (100) users(followers)
- Minimizes number of requests to API (current limit: 180 requests/interval)
- Example: User with 1000 followers will only require 10 requests to return entire dataset
 	
## Output 
- All data is rendered in a CSV dump format

## Note:
- If a requested user is unknown,suspended,or deleted, then that user will not be returned in the results list.
- You must be following a protected user to be able to see their most recent status update. If you don't follow a protected user their status will be hidden.
- You must edit auth.php and insert your credentials from your dev.twitter.com account
