const imageContainer = document.querySelector(".image-container");
const image = imageContainer.querySelector("img");
imageContainer.addEventListener("mousemove", (e) => {
    const { left, top, width, height } = imageContainer.getBoundingClientRect();
    const x = ((e.clientX - left) / width) * 100;
    const y = ((e.clientY - top) / height) * 100;
    
    image.style.transformOrigin = `${x}% ${y}%`;
    image.style.transform = "scale(1.3)";
});

imageContainer.addEventListener("mouseleave", () => {
    image.style.transformOrigin = "center center";
    image.style.transform = "scale(1)";
});