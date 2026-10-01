// hero slider
$(document).ready(function(){
    $('.hero_slider').slick({
        dots: true,
        arrows: true,
        infinite: true,
        speed: 800,
        // autoplay: true,
        autoplaySpeed: 3000,
        fade: false, 
        cssEase: 'ease-in-out',

        slidesToShow: 1,   
        slidesToScroll: 1
    });
});

// category slider
$(document).ready(function(){
    $('#categories_slider').slick({
        dots: false,
        arrows: false,
        infinite: true,
        speed: 800,
        autoplay: true,
        autoplaySpeed: 3000,
        cssEase: 'ease-in-out',

        slidesToShow: 8,   // desktop
        slidesToScroll: 1,

        responsive: [
            {
                breakpoint: 1024, // tablet
                settings: {
                    slidesToShow: 3
                }
            },
            {
                breakpoint: 768, // mobile
                settings: {
                    slidesToShow: 2,
                    arrows: false  // optional
                }
            },
            {
                breakpoint: 480, // small mobile
                settings: {
                    slidesToShow: 2,
                    arrows: false,
                    dots: true
                }
            }
        ]
    });
});

// selling product slider
$(document).ready(function(){

    $('.selling_prodslider').slick({
        slidesToShow: 4,
        slidesToScroll: 1,
        autoplay: true,
        arrows: true,
        dots: true,
        speed: 2000,
        infinite: true,
        cssEase: 'ease',

        responsive: [
            {
                breakpoint: 1200,
                settings: {
                    slidesToShow: 3
                }
            },

            {
                breakpoint: 1024, // tablet
                settings: {
                    slidesToShow: 3
                }
            },
            {
                breakpoint: 992,
                settings: {
                    slidesToShow: 2
                }
            },
            {
                breakpoint: 576,
                settings: {
                    slidesToShow: 1
                }
            }
        ]
    });

});



// mega menu scripts
function toggleMegaMenu() {
    const menu = document.getElementById("megaMenu");
    menu.style.display = menu.style.display === "block" ? "none" : "block";
}

// Close when clicking outside
document.addEventListener("click", function(e) {
    const menu = document.getElementById("megaMenu");
    const button = document.querySelector(".category_btn");

    if (!button.contains(e.target) && !menu.contains(e.target)) {
        menu.style.display = "none";
    }
});



const items = document.querySelectorAll(".mega_sidebar li");
const blocks = document.querySelectorAll(".mega_block");

items.forEach(item => {
    item.addEventListener("mouseenter", () => {

        // remove active
        items.forEach(i => i.classList.remove("active"));
        blocks.forEach(b => b.classList.remove("active"));

        // add active
        item.classList.add("active");

        const target = item.getAttribute("data-target");
        document.getElementById(target).classList.add("active");
    });
});



function toggleMenu() {
    const menu = document.querySelector(".nav_menu");
    const toggle = document.querySelector(".toggle_btn");

    menu.classList.toggle("active");
    toggle.classList.toggle("open");
}


function toggleCart() {
    const cart = document.getElementById("cartSidebar");
    const overlay = document.querySelector(".cart_overlay");

    cart.classList.toggle("active");
    overlay.classList.toggle("active");
}



// subcategory script signle category page
document.addEventListener("DOMContentLoaded", () => {

    // Close all on load
    /*document.querySelectorAll(".subcategory, .child_list").forEach(el => {
        el.classList.remove("open");
    });

    document.querySelectorAll(".arrow").forEach(el => {
        el.classList.remove("rotate");
    });*/

    // CATEGORY CLICK (full row clickable)
    document.querySelectorAll(".category_header").forEach(header => {
        header.addEventListener("click", () => {

            const parent = header.closest(".category");
            const sub = parent.querySelector(".subcategory");
            const arrow = header.querySelector(".arrow");

            if (sub) {
                sub.classList.toggle("open");
                arrow.classList.toggle("rotate");
            }
        });
    });

    // SUBCATEGORY CLICK (full row clickable)
    document.querySelectorAll(".subcategory_header").forEach(header => {
        header.addEventListener("click", () => {

            const parent = header.closest(".subcategory_item");
            const child = parent.querySelector(".child_list");
            const arrow = header.querySelector(".arrow");

            if (child) {
                child.classList.toggle("open");
                arrow.classList.toggle("rotate");
            }
        });
    });

});



document.addEventListener("DOMContentLoaded", function () {

    const minRange = document.getElementById("minRange");
    const maxRange = document.getElementById("maxRange");
    const priceValue = document.getElementById("priceValue");
    const rangeFill = document.getElementById("rangeFill");

    if (!minRange || !maxRange) return;

    function updateSlider() {
        let min = parseInt(minRange.value);
        let max = parseInt(maxRange.value);

        if (min > max) {
            [min, max] = [max, min];
        }

        priceValue.innerText = `$${min} - $${max}`;

        let minPercent = (min / minRange.max) * 100;
        let maxPercent = (max / maxRange.max) * 100;

        rangeFill.style.left = minPercent + "%";
        rangeFill.style.width = (maxPercent - minPercent) + "%";
    }

    minRange.addEventListener("input", updateSlider);
    maxRange.addEventListener("input", updateSlider);

    updateSlider();
});



// Thumbnail slider

$(document).ready(function(){

  $('.main-slider').slick({
    slidesToShow: 1,
    slidesToScroll: 1,
    arrows: true,
    fade: true,
    asNavFor: '.thumb-slider'
  });

  $('.thumb-slider').slick({
    slidesToShow: 4,
    slidesToScroll: 1,
    asNavFor: '.main-slider',
    focusOnSelect: true
  });

});


 // Quantity logic

/*document.querySelectorAll(".stepper").forEach(stepper => {
  let qty = 1;

  const qtyDisplay = stepper.querySelector(".qty");
  const plusBtn = stepper.querySelector(".plus");
  const minusBtn = stepper.querySelector(".minus");

  function updateQty() {
    qtyDisplay.textContent = qty.toString().padStart(2, '0');
  }

  plusBtn.addEventListener("click", () => {
    qty++;
    updateQty();
  });

  minusBtn.addEventListener("click", () => {
    if (qty > 1) qty--;
    updateQty();
  });
});*/



// tab js

const tabs = document.querySelectorAll(".tab-link");
const contents = document.querySelectorAll(".tab-content");

tabs.forEach(tab => {
  tab.addEventListener("click", () => {
    const target = tab.getAttribute("data-tab");

    // remove active classes
    tabs.forEach(t => t.classList.remove("active"));
    contents.forEach(c => c.classList.remove("active"));

    // add active classes
    tab.classList.add("active");
    document.getElementById(target).classList.add("active");
  });
});


// shipping form 

document.addEventListener("DOMContentLoaded", function () {
    const checkbox = document.getElementById("sameAddress");
    const form = document.getElementById("shippingaddress_form");

    if (checkbox) {
        checkbox.addEventListener("change", function () {
            form.style.display = this.checked ? "none" : "block";
        });
    }
});


// treanding brand slider

$(document).ready(function(){
    $('#trendingbrands_slider').slick({
        dots: false,
        arrows: false,
        infinite: true,
        speed: 800,
        autoplay: true,
        autoplaySpeed: 3000,
        cssEase: 'ease-in-out',

        slidesToShow: 6,   // desktop
        slidesToScroll: 1,

        responsive: [
            {
                breakpoint: 1024, // tablet
                settings: {
                    slidesToShow: 3
                }
            },
            {
                breakpoint: 768, // mobile
                settings: {
                    slidesToShow: 1,
                    arrows: false  // optional
                }
            },
            {
                breakpoint: 480, // small mobile
                settings: {
                    slidesToShow: 1,
                    arrows: false,
                    dots: true
                }
            }
        ]
    });
});



// Step form steps

// document.addEventListener("DOMContentLoaded", function () {

//   const container = document.querySelector(".useraccountmain_box");
//   if (!container) return;

//   /* ================= TABS ================= */
//   const tabBtns = container.querySelectorAll(".tab-btn");
//   const tabContents = container.querySelectorAll(".tab-content");

//   tabBtns.forEach(btn => {
//     btn.addEventListener("click", () => {

//       tabBtns.forEach(b => b.classList.remove("active"));
//       tabContents.forEach(c => c.classList.remove("active"));

//       btn.classList.add("active");

//       const tab = btn.getAttribute("data-tab");
//       container.querySelector("#" + tab).classList.add("active");

//       /* 🔥 HANDLE TRADE / GUEST WIDTH CONTROL */
//       if (tab === "trade") {
//         container.classList.add("trade-active");
//         showStep(currentStep); // apply correct width
//       } else {
//         container.classList.remove("trade-active");

//         // reset width
//         container.classList.remove(
//           "step-1-width",
//           "step-2-width",
//           "step-3-width",
//           "step-4-width"
//         );
//       }

//     });
//   });

//   /* ================= MULTI STEP (ONLY TRADE) ================= */
//   const formSteps = container.querySelectorAll("#trade .form-step");
//   const nextBtns = container.querySelectorAll("#trade .next");
//   const prevBtns = container.querySelectorAll("#trade .prev");
//   const stepIndicators = container.querySelectorAll("#trade .step");

//   let currentStep = 0;

//   function showStep(step) {

//     formSteps.forEach((el, i) => {
//       el.classList.toggle("active", i === step);
//     });

//     stepIndicators.forEach((el, i) => {
//       el.classList.toggle("active", i === step);
//     });

//     /* 🔥 APPLY WIDTH ONLY IF TRADE ACTIVE */
//     if (container.classList.contains("trade-active")) {

//       container.classList.remove(
//         "step-1-width",
//         "step-2-width",
//         "step-3-width",
//         "step-4-width"
//       );

//       container.classList.add(`step-${step + 1}-width`);
//     }
//   }

//   /* NEXT BUTTON */
//   nextBtns.forEach(btn => {
//     btn.addEventListener("click", () => {
//       if (currentStep < formSteps.length - 1) {
//         currentStep++;
//         showStep(currentStep);
//       }
//     });
//   });

//   /* PREV BUTTON */
//   prevBtns.forEach(btn => {
//     btn.addEventListener("click", () => {
//       if (currentStep > 0) {
//         currentStep--;
//         showStep(currentStep);
//       }
//     });
//   });

//   /* INIT */
//   showStep(0);

// });




// const nextBtns = document.querySelectorAll(".next");
// const prevBtns = document.querySelectorAll(".prev");
// const formSteps = document.querySelectorAll(".form-step");
// const stepItems = document.querySelectorAll(".step-item");

// let currentStep = 0;

// // Show step
// function updateFormSteps() {
//     formSteps.forEach((step, index) => {
//         step.classList.remove("active");
//         if (index === currentStep) {
//             step.classList.add("active");
//         }
//     });

//     updateProgress();
// }

// // Update progress UI
// function updateProgress() {
//     stepItems.forEach((item, index) => {
//         item.classList.remove("active", "completed");

//         if (index < currentStep) {
//             item.classList.add("completed");
//         } else if (index === currentStep) {
//             item.classList.add("active");
//         }
//     });
// }

// // NEXT
// nextBtns.forEach(btn => {
//     btn.addEventListener("click", () => {
//         if (currentStep < formSteps.length - 1) {
//             currentStep++;
//             updateFormSteps();
//         }
//     });
// });

// // PREV
// prevBtns.forEach(btn => {
//     btn.addEventListener("click", () => {
//         if (currentStep > 0) {
//             currentStep--;
//             updateFormSteps();
//         }
//     });
// });


// const stepsContainer = document.querySelector(".steps");

// function updateProgress() {
//     stepItems.forEach((item, index) => {
//         item.classList.remove("active", "completed");

//         if (index < currentStep) {
//             item.classList.add("completed");
//         } else if (index === currentStep) {
//             item.classList.add("active");
//         }
//     });

//     let progress = (currentStep) / (stepItems.length - 1) * 100;
//     stepsContainer.style.setProperty("--progress", progress + "%");
// }

// tab code with progress bar end


// table code from to to date 

const fromDate = document.getElementById("fromDate");
const toDate = document.getElementById("toDate");

// 👉 Open calendar when clicking anywhere in field
document.querySelectorAll(".field").forEach(field => {
  field.addEventListener("click", function () {
    const input = this.querySelector("input");

    // change to date if still text
    if (input.type === "text") {
      input.type = "date";
    }

    input.focus();

    // open picker (modern browsers)
    if (input.showPicker) {
      input.showPicker();
    }
  });
});

// 👉 Set min date for "To Date"
/*fromDate.addEventListener("change", () => {
  if (fromDate.value) {
    toDate.type = "date"; // ensure correct type
    toDate.min = fromDate.value;
  }
});*/

// 👉 Optional: prevent wrong selection manually
/*toDate.addEventListener("change", () => {
  if (toDate.value < fromDate.value) {
    alert("To Date cannot be earlier than From Date");
    toDate.value = "";
  }
});
*/


// my address checkbox

/*document.addEventListener("DOMContentLoaded", function () {
  const switches = document.querySelectorAll(".default-switch");

  switches.forEach(sw => {
    sw.addEventListener("change", function () {

      if (this.checked) {
        switches.forEach(other => {
          if (other !== this) {
            other.checked = false;
          }
        });
      } else {
        // at least ek ON rehna chahiye
        this.checked = true;
      }

    });
  });
});*/




/*let totalTime = (1 * 60 * 60) + (10 * 60) + 15; 
// 1 hour 10 minutes 15 seconds

function updateCountdown() {

    let hours = Math.floor(totalTime / 3600);
    let minutes = Math.floor((totalTime % 3600) / 60);
    let seconds = totalTime % 60;

    document.getElementById("hours").textContent = String(hours).padStart(2, '0');
    document.getElementById("minutes").textContent = String(minutes).padStart(2, '0');
    document.getElementById("seconds").textContent = String(seconds).padStart(2, '0');

    if (totalTime > 0) {
        totalTime--;
    } else {
        clearInterval(timer);
        alert("Time's up!");
    }
}

let timer = setInterval(updateCountdown, 1000);
updateCountdown();*/



// Account setting save address form 
/*document.addEventListener("DOMContentLoaded", function () {
    const checkbox = document.getElementById("changePass");
    const form = document.getElementById("passwordForm");

    // Default hidden (since unchecked)
    form.style.display = "none";

    checkbox.addEventListener("change", function () {
        form.style.display = this.checked ? "block" : "none";
    });
});*/
