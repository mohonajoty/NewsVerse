document.addEventListener('DOMContentLoaded', function() {
    
    // Get the button
    const mybutton = document.getElementById('backToTop');

    // When the user scrolls down 300px from the top of the document, show the button
    window.onscroll = function() {scrollFunction()};

    function scrollFunction() {
        if (document.body.scrollTop > 300 || document.documentElement.scrollTop > 300) {
            mybutton.classList.remove('d-none');
        } else {
            mybutton.classList.add('d-none');
        }
    }

    // When the user clicks on the button, scroll to the top of the document
    mybutton.addEventListener('click', function() {
        window.scrollTo({
            top: 0, 
            behavior: 'smooth'
        });
    });

});