(function($){
    
    $(document).ready(function() {

        
       /* ---------------------------------------------- /*
         * Home section height
         /* ---------------------------------------------- */

        function buildHomeSection(homeSection) {
            if (homeSection.length > 0) {
                if (homeSection.hasClass('home-full-height')) {
                    homeSection.height($(window).height());
                } else {
                    homeSection.height($(window).height() * 0.85);
                }
            }
        }
        

        /* ---------------------------------------------- /*
         * News highlight section 
        /* ---------------------------------------------- */ 
          $('#spnc-marquee-right').on('click', function() {
                 $('.spnc_highlights').addClass('right');
             })
             $('#spnc-marquee-left').on('click', function() {
                 $('.spnc_highlights').removeClass('right');
             })
                    
        /* ---------------------------------------------- /*
         * Scroll top
         /* ---------------------------------------------- */

        $(window).scroll(function() {
            if ($(this).scrollTop() > 100) {
                $('.scroll-up').fadeIn();
            } else {
                $('.scroll-up').fadeOut();
            }
        });

        $('a[href="#totop"]').click(function() {
            $('html, body').animate({ scrollTop: 0 }, 'slow');
            return false;
        });
    
        // Accodian Js
        function toggleIcon(e) {
            $(e.target)
            .prev('.panel-heading')
            .find(".more-less")
            .toggleClass('fa-plus-square-o fa-minus-square-o');
        }
        $('.panel-group').on('hidden.bs.collapse', toggleIcon);
        $('.panel-group').on('shown.bs.collapse', toggleIcon);

        // top date time
        var timeElement = $( ".newscrunch-topbar-time" )
        if( timeElement.length > 0 ) {
            setInterval(function() {
                timeElement.html(new Date().toLocaleTimeString())
            },1000);
        }

        /* ---------------------------------------------- /*
         * Sticky Header
         /* ---------------------------------------------- */
        jQuery(window).bind('scroll', function () {
            if ( jQuery(window).scrollTop() > 100) {
                jQuery('.header-sticky').addClass('stickymenu');
                jQuery('.wow-callback').addClass('wow-sticky');
                jQuery('body').addClass('spnc-ad-sticky');
            } else {
                jQuery('.header-sticky').removeClass('stickymenu');
                jQuery('.wow-callback').removeClass('wow-sticky');
                jQuery('body').removeClass('spnc-ad-sticky');
            }
        });

        /* ---------------------------------------------- /*
         * Banner carousel 
        /* ---------------------------------------------- */  
        $("#spnc-banner-carousel-1").owlCarousel({
            navigation : true, // Show next and prev buttons        
            autoplay: true,
            autoplayTimeout: 3000,
            autoplayHoverPause: true,
            smartSpeed: 2300,
            loop:true, // loop is true up to 1199px screen.
            nav:true, // is true across all sizes
            margin:10, // margin 10px till 960 breakpoint
            autoHeight: true,
            responsiveClass:true, // Optional helper class. Add 'owl-reponsive-' + 'breakpoint' class to main element.
            //items: 3,
            dots: false,
            navText: ["<i class='fa fa-arrow-left'></i>","<i class='fa fa-arrow-right'></i>"],
            responsive:{    
                200:{ items:1 },
                480:{ items:1 },
                768:{ items:1 },
                1000:{ items:1 }            
            }
        });

        // Video section popup
        $('.spnc-popup-youtube').magnificPopup({
            disableOn: 700,
            type: 'iframe',
            mainClass: 'mfp-fade',
            removalDelay: 160,
            preloader: false,
            fixedContentPos: false
        });



      // Custom MP4 Video Popup (inline)
    $('.spnc-popup-custom-video').magnificPopup({
      type: 'inline',
      midClick: true,
      mainClass: 'mfp-fade',
      removalDelay: 160,
      fixedContentPos: false,
      closeBtnInside: true,
      callbacks: {
        open: function () {
          const video = document.getElementById('spncCustomVideo');
          video.pause(); // Ensure paused when opened
        },
        close: function () {
          const video = document.getElementById('spncCustomVideo');
          video.pause();
          video.currentTime = 0;
        }
      }
    });



        /* ---------------------------------------------- /*
         * Sticky sidebar
        /* ---------------------------------------------- */    
        var topLimit = ($('#spnc-sidebar-fixed').offset() || { "top": NaN }).top;
        //var bottomLimit=$(".spnc-blog-section").height();
        $(window).scroll(function() {
            if (topLimit <= $(window).scrollTop()){
                $('#spnc-sidebar-fixed').addClass('spnc_sticky')
            } else {
                $('#spnc-sidebar-fixed').removeClass('spnc_sticky')
            }
        });
        $(document).ready(function() {
            $('.spnc-sticky-sidebar, .spnc-sticky-content')
                .theiaStickySidebar({
                    additionalMarginTop: 30
            });
        });

        /* ---------------------------------------------- /*
         *Add animation class in widget
        /* ---------------------------------------------- */ 
        //add css for post title hover effect
        var className = $('#wrapper #page').attr('class');
        if (typeof className !== 'undefined') {
        // Access properties or methods of someVariable here

        className= className.split(' ');
        $(".wp-block-latest-posts__post-title").addClass(className[1]);

        //add css for img hover effect
        const footerClass = className[2].split('-');
        $(".wp-block-latest-posts__featured-image").addClass(footerClass[1]);
        }

    }); 

    /* Preloader */
    jQuery(window).on('load', function() {
        var preloaderFadeOutTime = 500;
        function newscrunch_hidePreloader() {
            var preloader = jQuery('.newscrunch-loader');
            setTimeout(function() {
                preloader.fadeOut(preloaderFadeOutTime);
            }, 500);
        }
        newscrunch_hidePreloader();
    });

})(jQuery); 

/* ---------------------------------------------- /*
         * Sidebar Panel
/* ---------------------------------------------- */
function spncOpenPanel() {
    document.getElementById("spnc_panelSidebar").style.transform = "translate3d(0,0,0)";
    document.getElementById("spnc_panelSidebar").style.visibility = "visible";
    document.getElementById("wrapper").style.marginLeft = "0px";
    document.body.classList.add("spnc_body_sidepanel");
    const panel_elm = document.getElementById('spnc_panelSidebar');
    panel_elm.addEventListener('focusout', (event1) => {
        if(panel_elm.contains(event1.relatedTarget)==false){
            document.getElementById("spnc_panelSidebar").style.transform = "";
            document.getElementById("spnc_panelSidebar").style.visibility = "hidden";
            document.getElementById("wrapper").style.marginLeft = "0px";
            document.body.classList.remove("spnc_body_sidepanel");
        }
    })
}
function spncClosePanel() {
    document.getElementById("spnc_panelSidebar").style.transform = "";
    document.getElementById("spnc_panelSidebar").style.visibility = "hidden";
    document.getElementById("wrapper").style.marginLeft = "0px";
    document.body.classList.remove("spnc_body_sidepanel");
}


jQuery(document).ready(function($) {
     // When the window resizes
    $(window).on('resize', function () {
         // Get the height + padding + border of `#masthead`
         var headerHeight = $('header').outerHeight();
         var incrseHeight=  headerHeight-185;
       //  Add the height to `.page-title-section`
        $('.page-title-section').css('padding-top', incrseHeight+230);
        $('.newscrunch-plus:not(.newsblogger) .page-title-section.breadcrumb-2').css('padding-top', incrseHeight+210);
        $('.newsblogger .page-title-section').css('padding-top', 'unset');
        $('.newscrunch-plus.newsblogger .page-title-section').css('padding-top', incrseHeight+230);
        $('.newscrunch-plus.newsblogger .page-title-section.breadcrumb-2').css('padding-top', incrseHeight+210);
        $('.newscrunch-plus section.bread-nt-2.disable').css('padding-top', incrseHeight+210);
    });
     // Trigger the function on document load.
    $(window).trigger('resize');
});


/* ---------------------------------------------- /*
         * open and close sidemenu in responsive
/* ---------------------------------------------- */
let MenucodeVisible = false;
function openNav() {
    MenucodeVisible= true;
    updateMenuFocusVisibility();
    var element = document.getElementById("spnc-menu-open");
    element.classList.add("open");
    jQuery('body').addClass('off-canvas');
     const panel_elm1 = document.getElementById('spnc-menu-open');
    panel_elm1.addEventListener('focusout', (event) => {
        if(panel_elm1.contains(event.relatedTarget)==false){
            panel_elm1.classList.remove("open");
             jQuery('body').removeClass('off-canvas');
            document.getElementsByClassName("spnc-menu-open")[0].focus();
            MenucodeVisible= false;
            updateMenuFocusVisibility();
        }
    })
}
function closeNav() {
     MenucodeVisible=false;
    updateMenuFocusVisibility();
    var element = document.getElementById("spnc-menu-open");
    element.classList.remove("open");
    jQuery('body').removeClass('off-canvas');
    document.getElementsByClassName("spnc-menu-open")[0].focus();
}
jQuery(".spnc-nav-menu-overlay").on('click', function () {
    var element124 = document.getElementById("spnc-menu-open");
    element124.classList.remove("open");
    jQuery('body').removeClass('off-canvas');
})
function updateMenuFocusVisibility() {
    if (MenucodeVisible) {
        // Show focus
       document.getElementById("spnc-menu-open").style.display = 'block';
    } else {
        // Hide focus
        document.getElementById("spnc-menu-open").style.display = 'none';
    }
}
updateMenuFocusVisibility();
// open and close sidemenu

// js for category panel open and close start---------------------------------------------------
document.addEventListener("DOMContentLoaded", function () {
  const dropdownWrapper = document.getElementById("shop-product-cat-search");
   if (!dropdownWrapper) {
    // Element not found, stop script
    return;
  }
  const trigger = dropdownWrapper.querySelector(".shop-product-cat-selected"); 
  const dropdown = dropdownWrapper.querySelector(".shop-category-list");
  const items = dropdown.querySelectorAll("li[role='menuitem']");
  const shopCloseBtn = dropdownWrapper.querySelector(".shop-cat-close"); 
  const hiddenInput = document.getElementById("selected-product-cat"); 
  const categoryLinks = dropdownWrapper.querySelectorAll(".shop-category-list ul li a");
  const catIcon = dropdownWrapper.querySelector("i.fa-chevron-down");

  let currentIndex = -1;

  // --- Utility functions ---
  function openDropdown() {
    dropdownWrapper.classList.add("open");
    dropdown.setAttribute("aria-hidden", "false");
    currentIndex = 0;

    if (items.length > 0) {
      items[currentIndex].querySelector("a").focus();
    }
  }

  function closeDropdown() {
    dropdownWrapper.classList.remove("open");
    dropdown.setAttribute("aria-hidden", "true");
    currentIndex = -1;
    trigger.focus();
  }

  function selectItem(item) {
    const link = item.querySelector("a");
    const value = link.getAttribute("cat-value");
    const text = link.textContent.trim();

    trigger.textContent = text;
    hiddenInput.value = value;
    closeDropdown();
  }

  // --- Event listeners ---

  // Mouse click on category link
  categoryLinks.forEach(function (link) {
    link.addEventListener("click", function (e) {
      e.preventDefault();
      const catName = this.textContent;
      const catSlug = this.getAttribute("cat-value");

      trigger.textContent = catName;
      hiddenInput.value = catSlug;
      dropdownWrapper.classList.remove("open");
    });
  });
   if (catIcon) {
            [trigger, catIcon].forEach(function (element) {
                element.addEventListener("click", function (e) {
                    e.stopPropagation();
                    dropdownWrapper.classList.toggle("open");
                });
            });
        }

  // Trigger button click
  trigger.addEventListener("click", (e) => {
    e.stopPropagation();
    dropdownWrapper.classList.contains("open") ? closeDropdown() : openDropdown();
  });

  // Close button mouse click
  shopCloseBtn.addEventListener("click", (e) => {
    e.stopPropagation();
    closeDropdown();
  });

  // Close button keyboard (Enter / Space)
  shopCloseBtn.addEventListener("keydown", (e) => {
    if (e.key === "Enter" || e.key === " ") {
      e.preventDefault();
      e.stopPropagation();
      closeDropdown();
    }
  });

  // Mouse click on item
  items.forEach((item) => {
    item.addEventListener("click", (e) => {
      e.preventDefault();
      selectItem(item);
    });
  });

  // Sync currentIndex when links are focused (Tab navigation or mouse focus)
  items.forEach((item, index) => {
    const link = item.querySelector("a");
    link.addEventListener("focus", () => {
      currentIndex = index;
    });
  });
  // close full dropdown when focus leaves panel
      if (dropdownWrapper) {
        dropdownWrapper.addEventListener('focusout', () => {
          setTimeout(() => {
            if (!dropdownWrapper.contains(document.activeElement)) {
              closeDropdown();
            }
          }, 0);
        });
      }
  // Keyboard navigation inside dropdown
  dropdownWrapper.addEventListener("keydown", (e) => {
    if (!dropdownWrapper.classList.contains("open") &&
        (e.key === "Enter" || e.key === " " || e.key === "ArrowDown")) {
      e.preventDefault();
      openDropdown();
      return;
    }

    if (dropdownWrapper.classList.contains("open")) {
      if (e.key === "ArrowDown") {
        e.preventDefault();
        currentIndex = (currentIndex + 1) % items.length;
        items[currentIndex].querySelector("a").focus();
      } else if (e.key === "ArrowUp") {
        e.preventDefault();
        currentIndex = (currentIndex - 1 + items.length) % items.length;
        items[currentIndex].querySelector("a").focus();
      } else if (e.key === "Enter" || e.key === " ") {
        e.preventDefault();
        const currentLink = items[currentIndex].querySelector("a");
        currentLink.click(); // triggers selection
      } else if (e.key === "Escape") {
        e.preventDefault();
        closeDropdown();
      }
    }
  });

  // Close dropdown on outside click
  document.addEventListener("click", (e) => {
    if (dropdownWrapper.classList.contains("open") && !dropdownWrapper.contains(e.target)) {
      closeDropdown();
    }
  });
});

// js for category panel open and close Ends---------------------------------------------------

// header category select open js Start-------------------------------------------------------------
// Run after DOM is ready (add inside your existing DOMContentLoaded / IIFE)
(function () {
  document.addEventListener("DOMContentLoaded", function () {
    const menus = document.querySelectorAll(".shop-cat-menu");
    if (!menus.length) return;

    menus.forEach((menuWrapper, idx) => {
      const toggleBtn = menuWrapper.querySelector(".shop-cat-menu-head");
      const panel = menuWrapper.querySelector(".shop-cat-card");
      const items = panel ? panel.querySelectorAll("a") : [];
      let currentIndex = -1;

      // accessibility setup
      if (toggleBtn && !toggleBtn.hasAttribute("tabindex")) toggleBtn.setAttribute("tabindex", "0");
      toggleBtn && toggleBtn.setAttribute("role", "button");
      toggleBtn && toggleBtn.setAttribute("aria-expanded", "false");

      const panelId = panel.id || `shop-cat-card-${idx}`;
      panel && panel.setAttribute("id", panelId);
      toggleBtn && toggleBtn.setAttribute("aria-controls", panelId);
      panel && panel.setAttribute("aria-hidden", "true");

      function openMenu() {
        menuWrapper.classList.add("open");
        panel && panel.setAttribute("aria-hidden", "false");
        toggleBtn && toggleBtn.setAttribute("aria-expanded", "true");
        currentIndex = (items.length > 0) ? 0 : -1;
        if (items.length > 0 && items[0].focus) items[0].focus();
      }

      function closeMenu() {
        menuWrapper.classList.remove("open");
        panel && panel.setAttribute("aria-hidden", "true");
        toggleBtn && toggleBtn.setAttribute("aria-expanded", "false");
        currentIndex = -1;
      }

      function toggleMenu() {
        menuWrapper.classList.contains("open") ? closeMenu() : openMenu();
      }

      // click toggle
      toggleBtn && toggleBtn.addEventListener("click", (ev) => {
        ev.stopPropagation();
        toggleMenu();
      });

      // keyboard toggle
      toggleBtn && toggleBtn.addEventListener("keydown", (ev) => {
        const key = ev.key;
        if (key === "Enter" || key === " ") {
          ev.preventDefault();
          toggleMenu();
        } else if (key === "ArrowDown") {
          ev.preventDefault();
          if (!menuWrapper.classList.contains("open")) {
            openMenu();
          } else if (items.length) {
            currentIndex = 0;
            items[currentIndex].focus();
          }
        }
      });

      // keyboard navigation inside
      menuWrapper.addEventListener("keydown", (ev) => {
        const key = ev.key;
        if (!menuWrapper.classList.contains("open") && (key === "Enter" || key === " " || key === "ArrowDown")) {
          ev.preventDefault();
          openMenu();
          return;
        }
        if (!menuWrapper.classList.contains("open")) return;

        if (key === "ArrowDown") {
          ev.preventDefault();
          if (!items.length) return;
          currentIndex = (currentIndex + 1) % items.length;
          items[currentIndex].focus();
        } else if (key === "ArrowUp") {
          ev.preventDefault();
          if (!items.length) return;
          currentIndex = (currentIndex - 1 + items.length) % items.length;
          items[currentIndex].focus();
        } else if (key === "Escape") {
          ev.preventDefault();
          closeMenu();
          if (toggleBtn && toggleBtn.focus) toggleBtn.focus();
        }
      });

      // submenu open/close on focus
      const parentItems = panel.querySelectorAll('.cat-item');
      parentItems.forEach((li) => {
        const submenu = li.querySelector('.children, .sub-menu');
        if (!submenu) return;

        li.addEventListener('focusin', () => {
          li.classList.add('keyboard-open');
          const a = li.querySelector('a');
          if (a) a.setAttribute('aria-expanded', 'true');
        });

        li.addEventListener('focusout', () => {
          setTimeout(() => {
            if (!li.contains(document.activeElement)) {
              li.classList.remove('keyboard-open');
              const a = li.querySelector('a');
              if (a) a.setAttribute('aria-expanded', 'false');
            }
          }, 0);
        });
      });

      //  close full dropdown when focus leaves panel
      if (panel) {
        panel.addEventListener('focusout', () => {
          setTimeout(() => {
            if (!panel.contains(document.activeElement)) {
              closeMenu();
            }
          }, 0);
        });
      }

      // close on outside click
      document.addEventListener("click", (ev) => {
        if (menuWrapper.classList.contains("open") && !menuWrapper.contains(ev.target)) {
          closeMenu();
        }
      });
    });
  });
})();

// header category select open js Ends-------------------------------------------------------------