// rate a comment when clicked on the corresponding comment & rating
const rateComment = function() {
    const rating = this.getAttribute('data-rating');
    const parentElement = this.closest('.rating');
    const commentId = parentElement.getAttribute('data-comment-id');

    saveRating(rating,commentId).then((data) => {
        let averageElement = parentElement.getElementsByClassName('average')[0];

        averageElement.innerHTML = 'Score: ' + data.result.new_average;
    }).catch(error => console.error(error));
};

//save the rating in the backend
const saveRating = function(rating, commentId) {
    if (! rating || !commentId) {
        return false;
    }

    let formData = new FormData();

    formData.append('rating', rating);
    formData.append('commentId', commentId);

    const options = {
        method: "POST",
        body: formData,
    };
    return fetch('/ajax/save_rating.php', options).then(response => response.json())
};

const sortComments = function () {
    const newSort = this.getAttribute('data-sort-type');
    const urlParams = new URLSearchParams(window.location.search);
    const articleId = urlParams.get('articleId');
    let currentSort = urlParams.get('sort');

    currentSort = newSort;

    window.location = "/index.php?articleId=" + articleId + "&sort=" + currentSort;
};

//add click-event listener on all the .rate-classes
let ratingButton = document.getElementsByClassName('rate');
Array.from(ratingButton).forEach(function(element) {
    element.addEventListener('click', rateComment);
});

//sort comments by newest comment-thread
const sortAscButton = document.getElementById('sort_ascending');
const sortDescButton = document.getElementById('sort_descending');
sortAscButton.addEventListener('click', sortComments);
sortDescButton.addEventListener('click', sortComments);
