document.addEventListener('DOMContentLoaded', function() {
    const starRatings = document.querySelectorAll('.star-rating');
    starRatings.forEach(function(starRating) {
        const stars = starRating.querySelectorAll('.star');
        stars.forEach(function(star, index) {
            star.addEventListener('click', function() {
                stars.forEach(function(star) {
                    star.classList.remove('active');
                });
                for (let i = 0; i <= index; i++) {
                    stars[i].classList.add('active');
                }
            });
        });
    });
});

$(document).ready(function() {
    // Wenn das Anmelde-Symbol geklickt wird
    $('#loginIcon').click(function() {
        // Öffnen Sie das Anmelde-Modal
        $('#anmeldeModal').modal('show');
    });
});
