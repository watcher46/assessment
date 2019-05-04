let ratingButton = document.getElementsByClassName('rate');

const rateComment = function() {
    let rating = this.getAttribute('data-rating');
    let parentElement = this.closest('.rating');
    let commentId = parentElement.getAttribute('data-comment-id');

    saveRating(rating,commentId).then((data) => {
        let averageElement = parentElement.getElementsByClassName('average')[0];

        averageElement.innerHTML = 'Score: ' + data.result.new_average;
    }).catch(error => console.error(error));
};

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

Array.from(ratingButton).forEach(function(element) {
    element.addEventListener('click', rateComment);
});
