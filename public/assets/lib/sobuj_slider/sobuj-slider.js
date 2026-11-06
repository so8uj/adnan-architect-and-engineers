// guarded: with Turbo Drive this file is executed again on every visit to a page that includes it
window.SobujSlider = window.SobujSlider || class SobujSlider {
  constructor(selector, options = {}) {
    this.container = document.querySelector(selector);
    if (!this.container) return;

    this.options = {
      duration: options.duration || 6000,
      transitionSpeed: 1000,
      columns: options.columns || 4,
      slides: options.slides || [],
      height: options.height || "100vh",
      arrows: options.arrows !== undefined ? options.arrows : true,
      dots: options.dots !== undefined ? options.dots : true,
      ...options,
    };

    this.currentSlide = -1; // Start at -1 to allow first slide animation
    this.isTransitioning = false;
    this.init();
  }

  init() {
    this.container.classList.add("sobuj-slider");
    this.container.style.height = this.options.height;
    this.container.style.setProperty("--ss-cols", this.options.columns);

    this.buildHTML();
    this.slides = this.container.querySelectorAll(".ss-slide");
    this.setupNavigation();

    // TRIGGER ANIMATION ON PAGE LOAD
    // Small timeout ensures the browser has painted the initial state
    setTimeout(() => {
      this.goToSlide(0);
      this.startAutoPlay();
    }, 100);
  }

  buildHTML() {
    let html = '<div class="ss-container">';
    this.options.slides.forEach((slide, index) => {
      let slotsHTML = '<div class="ss-bg-wrapper">';

      for (let i = 0; i < this.options.columns; i++) {
        const imgOffset = -(i * 100);

        slotsHTML += `
                    <div class="ss-slot">
                        <div class="ss-slot-img" style="
                            background-image: url('${slide.image}'); 
                            background-position: ${slide.pos || 'center'};
                            left: ${imgOffset}%;
                        "></div>
                        <div class="ss-slot-mask-wrapper">
                            <div class="ss-slot-mask" style="
                                background-image: url('${slide.image}');
                                background-position: ${slide.pos || 'center'};
                                left: ${imgOffset}%;
                            "></div>
                        </div>
                    </div>
                `;
      }
      slotsHTML += "</div>";

      html += `
                <div class="ss-slide">
                    ${slotsHTML}
                    <div class="ss-inner"><div class="ss-content">
                      <div>
                       <div><h1 class="ss-title">${slide.title}</h1>
                        ${slide.description ? `<p class="ss-desc">${slide.description}</p>` : ""}
                       </div>
                       <div class="ss-actions">
                       ${slide.btnText ? `<a href="${slide.btnLink || "#"}" class="ss-btn">${slide.btnText}</a>` : ""}
                       </div>
                      </div>
                      ${this.options.contactText ? `<div><a href="${this.options.contactLink || "#"}" class="contact-btn">${this.options.contactIcon ? `<img src="${this.options.contactIcon}" width="28" height="28">` : ""} <span>${this.options.contactText}</span></a></div>` : ""} 
                    </div></div>
                </div>
            `;
    });
    html += "</div>";

    if (this.options.arrows) {
      html += `
                <div class="ss-nav">
                    <button class="prev" type="button" aria-label="Previous slide"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 5l-7 7 7 7"/></svg></button>
                    <button class="next" type="button" aria-label="Next slide"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 5l7 7-7 7"/></svg></button>
                </div>
            `;
    }

    if (this.options.dots) {
      html += `<div class="ss-dots"></div>`;
    }

    this.container.innerHTML = html;
  }

  setupNavigation() {
    if (this.options.arrows) {
      const nextBtn = this.container.querySelector(".next");
      const prevBtn = this.container.querySelector(".prev");
      if (nextBtn) nextBtn.onclick = () => this.nextSlide();
      if (prevBtn) prevBtn.onclick = () => this.prevSlide();
    }

    if (this.options.dots) {
      const dots = this.container.querySelector(".ss-dots");
      if (dots) {
        this.slides.forEach((_, i) => {
          const dot = document.createElement("span");
          dot.className = `ss-dot`;
          dot.onclick = () => this.goToSlide(i);
          dots.appendChild(dot);
        });
      }
    }
  }

  goToSlide(index) {
    if (index === this.currentSlide || this.isTransitioning) return;
    this.isTransitioning = true;

    const prevIndex = this.currentSlide;
    const prev = prevIndex >= 0 ? this.slides[prevIndex] : null;
    this.currentSlide = (index + this.slides.length) % this.slides.length;
    const next = this.slides[this.currentSlide];

    const nextData = this.options.slides[this.currentSlide];
    const prevData = prevIndex >= 0 ? this.options.slides[prevIndex] : null;

    const slotImgs = next.querySelectorAll(".ss-slot-img");
    const slotMasks = next.querySelectorAll(".ss-slot-mask");

    if (!prev) {
      // First load: main image is black, mask is NEW image
      slotImgs.forEach((img) => {
        img.style.backgroundImage = "none";
        img.style.backgroundColor = "#0a0a0aff";
      });
      slotMasks.forEach((mask) => {
        mask.style.backgroundColor = "transparent";
        mask.style.backgroundImage = `url('${nextData.image}')`;
      });
    } else {
      // Transition: main image is OLD, mask is NEW
      slotImgs.forEach((img) => {
        img.style.backgroundImage = `url('${prevData.image}')`;
      });
      slotMasks.forEach((mask) => {
        mask.style.backgroundColor = "transparent";
        mask.style.backgroundImage = `url('${nextData.image}')`;
      });
    }

    if (prev) {
      prev.classList.remove("active");
      prev.classList.add("leaving");
      setTimeout(
        () => prev.classList.remove("leaving"),
        this.options.transitionSpeed,
      );
    }

    next.classList.add("active");

    if (this.options.dots) {
      const dotEls = this.container.querySelectorAll(".ss-dot");
      if (dotEls.length) {
        dotEls.forEach((d, i) =>
          d.classList.toggle("active", i === this.currentSlide),
        );
      }
    }

    setTimeout(() => {
      // After mask completes, replace main image with NEW image
      slotImgs.forEach((img) => {
        img.style.backgroundColor = "transparent";
        img.style.backgroundImage = `url('${nextData.image}')`;
      });
      this.isTransitioning = false;
    }, this.options.transitionSpeed);
  }

  nextSlide() {
    this.goToSlide(this.currentSlide + 1);
  }
  prevSlide() {
    this.goToSlide(this.currentSlide - 1);
  }

  startAutoPlay() {
    if (this.timer) clearInterval(this.timer);
    this.timer = setInterval(() => this.nextSlide(), this.options.duration);
  }
};
