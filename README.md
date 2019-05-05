# Technical Assessment

## Installation
- First follow the steps in the `.docker/README.md` file. 
- Go to `Adminer` on port 8080
 ```
 http:localhost:8080                // for docker-for-mac
 http://comments.tweakers.test:8080 // for docker-toolbox
 ```
- Credentials can be found in the docker-compose file.
- Import the sql from `./db/tweakers.sql` into the database.
- Open a new browser and go to: `https:/localhost/index.php?articleId=1` or `https://comments.tweakers.test/index.php?articleId=1`

## Add a comment
- Open your browser or Postman
- Fill in this url `https://comments.tweakers.test/ajax/create_comment.php?articleId={article-id}&description={comment-description}`
- Replace `article-id` with the id of the article
- Replace `comment-description` with the actuel content of the comment make sure the comment is url-encoded.
- Optional is to add another parameter `parent_comment_id` with should be the id of the comment you want to respond to. The id's of the comments are shown next to rate-options.
- Make sure the request is a GET-request. You'll get feedback if the comment is saved.
- Refresh the article page, your comment is now visible. 
