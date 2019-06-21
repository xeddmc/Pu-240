let prevScrollpos = window.pageYOffset;
window.onscroll = function () {
    let currentScrollPos = window.pageYOffset;
    if (prevScrollpos > currentScrollPos) {
        document.getElementById('navbar').style.top = '0';
        document.getElementById('hamburger').style.top = '15px';
    } else {
        document.getElementById('navbar').style.top = '-40px';
        document.getElementById('hamburger').style.top = '-40px';
    }
    prevScrollpos = currentScrollPos;
};