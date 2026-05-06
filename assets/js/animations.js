document.addEventListener("DOMContentLoaded", function () {
    var cards = document.querySelectorAll(".panel, .slot-card, .stat-card");
    cards.forEach(function (card, index) {
        card.style.opacity = "0";
        card.style.transform = "translateY(8px)";
        setTimeout(function () {
            card.style.transition = "opacity .4s ease, transform .4s ease";
            card.style.opacity = "1";
            card.style.transform = "translateY(0)";
        }, 60 * index);
    });
});

