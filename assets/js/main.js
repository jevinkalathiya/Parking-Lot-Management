// add hovered class to selected list item
/* let list = document.querySelectorAll(".navigation li");

function activeLink() {
  list.forEach((item) => {
    item.classList.remove("active");
  });
  this.classList.add("active");
}


list.forEach((item) => item.addEventListener("mouseover", activeLink)); */

// Select all list items in the navigation
let list = document.querySelectorAll(".navigation li .nav");

// Function to add 'active' class to the clicked list item
function activeLink(event) {
  // Remove 'active' class from all list items
  list.forEach((item) => item.classList.remove("active"));
  
  // Add 'active' class to the clicked list item
  event.currentTarget.classList.add("active");
}

// Add click event listeners to each list item
list.forEach((item) => item.addEventListener("click", activeLink));


// Menu Toggle
let toggle = document.querySelector(".toggle");
let navigation = document.querySelector(".navigation");
let main = document.querySelector(".main");

toggle.onclick = function () {
  navigation.classList.toggle("active");
  main.classList.toggle("active");
};

