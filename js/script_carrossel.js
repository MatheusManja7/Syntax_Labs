// JS - Carrossel da section soluções 
const track = document.getElementById('carousel_track');
const slides = document.querySelectorAll('.carousel_slide');
const dotsContainer = document.getElementById('carousel_dots');
const prevBtn = document.querySelector('.carousel_arrow.prev');
const nextBtn = document.querySelector('.carousel_arrow.next');

let currentIndex = 0;

slides.forEach((_, i) => {
    const dot = document.createElement('div');
    dot.classList.add('carousel_dot');
    if (i === 0) dot.classList.add('active');
    dot.addEventListener('click', () => goToSlide(i));
    dotsContainer.appendChild(dot);
});

const dots = document.querySelectorAll('.carousel_dot');

function updateCarousel() {
    track.style.transform = `translateX(-${currentIndex * 100}%)`;
    dots.forEach((dot, i) => dot.classList.toggle('active', i === currentIndex));
}

function goToSlide(index) {
    currentIndex = index;
    updateCarousel();
}

function nextSlide() {
    currentIndex = (currentIndex + 1) % slides.length;
    updateCarousel();
}

function prevSlide() {
    currentIndex = (currentIndex - 1 + slides.length) % slides.length;
    updateCarousel();
}

nextBtn.addEventListener('click', nextSlide);
prevBtn.addEventListener('click', prevSlide);

// Autoplay (opcional - remova se não quiser)
let autoplay = setInterval(nextSlide, 6000);

document.querySelector('.carousel').addEventListener('mouseenter', () => clearInterval(autoplay));
document.querySelector('.carousel').addEventListener('mouseleave', () => {
    autoplay = setInterval(nextSlide, 6000);
});