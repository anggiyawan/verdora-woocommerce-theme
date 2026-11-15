/**
 * Verdora front-end interactions. Dependency-free, loaded with `defer`.
 *
 * @author Priyana Anggiyawan
 */
(function () {
	'use strict';

	var config = window.verdoraConfig || { i18n: {} };
	var i18n = config.i18n || {};
	var body = document.body;
	var lastTrigger = null;

	/* Sticky header shadow + back-to-top -------------------------------- */
	var header = document.querySelector('.vd-header');
	var toTop = document.querySelector('.vd-totop');
	var ticking = false;

	function onScroll() {
		var y = window.scrollY || window.pageYOffset;
		if (header) { header.classList.toggle('is-scrolled', y > 8); }
		if (toTop) { toTop.hidden = y < 600; }
		ticking = false;
	}
	window.addEventListener('scroll', function () {
		if (!ticking) { window.requestAnimationFrame(onScroll); ticking = true; }
	}, { passive: true });
	onScroll();

	if (toTop) {
		toTop.addEventListener('click', function () { window.scrollTo({ top: 0, behavior: 'smooth' }); });
	}

	/* Off-canvas panels (mobile menu, shop filters) ---------------------- */
	function setPanel(id, open) {
		var panel = document.getElementById(id);
		if (!panel) { return; }
		panel.classList.toggle('is-open', open);
		panel.setAttribute('aria-hidden', open ? 'false' : 'true');
		body.classList.toggle('vd-lock', open);

		document.querySelectorAll('[data-vd-close="' + id + '"][hidden], [data-vd-close="' + id + '"].vd-shop-overlay').forEach(function (el) {
			el.hidden = !open;
		});
		document.querySelectorAll('[data-vd-open="' + id + '"]').forEach(function (el) {
			el.setAttribute('aria-expanded', open ? 'true' : 'false');
		});

		if (open) {
			var focusable = panel.querySelector('button, a[href], input, select');
			if (focusable) { setTimeout(function () { focusable.focus(); }, 50); }
		} else if (lastTrigger) {
			lastTrigger.focus();
			lastTrigger = null;
		}
	}

	document.addEventListener('click', function (e) {
		var opener = e.target.closest('[data-vd-open]');
		if (opener) {
			e.preventDefault();
			lastTrigger = opener;
			setPanel(opener.getAttribute('data-vd-open'), true);
			return;
		}
		var closer = e.target.closest('[data-vd-close]');
		if (closer) {
			e.preventDefault();
			setPanel(closer.getAttribute('data-vd-close'), false);
		}
	});

	document.addEventListener('keydown', function (e) {
		if (e.key !== 'Escape') { return; }
		document.querySelectorAll('.vd-drawer.is-open, .vd-shop-sidebar.is-open').forEach(function (panel) {
			setPanel(panel.id, false);
		});
	});

	// Close the filter panel if the viewport grows to desktop while it is open.
	window.matchMedia('(min-width: 901px)').addEventListener('change', function (mq) {
		if (mq.matches) {
			document.querySelectorAll('.vd-drawer.is-open, .vd-shop-sidebar.is-open').forEach(function (panel) {
				setPanel(panel.id, false);
			});
		}
	});

	/* Navbar dropdown submenus ------------------------------------------ */
	// CSS opens submenus on :hover / :focus-within. JS adds aria-expanded sync,
	// Escape to close, tap-to-open on touch screens, and edge flipping.
	(function () {
		var parents = document.querySelectorAll('.vd-catnav__list > .menu-item-has-children');
		if (!parents.length) { return; }
		var lastPointer = 'mouse';

		function link(li) { return li.querySelector(':scope > a'); }
		function sub(li) { return li.querySelector(':scope > .sub-menu'); }

		function setExpanded(li, open) {
			var a = link(li);
			if (a) { a.setAttribute('aria-expanded', open ? 'true' : 'false'); }
			if (open) {
				var menu = sub(li);
				if (menu) {
					menu.classList.remove('is-flipped');
					if (menu.getBoundingClientRect().right > window.innerWidth - 8) { menu.classList.add('is-flipped'); }
				}
			}
		}

		function closeAll(except) {
			parents.forEach(function (li) {
				if (li !== except) { li.classList.remove('is-open'); setExpanded(li, false); }
			});
		}

		document.addEventListener('pointerdown', function (e) { lastPointer = e.pointerType || 'mouse'; }, true);

		parents.forEach(function (li) {
			li.addEventListener('mouseenter', function () { li.classList.remove('is-dismissed'); setExpanded(li, true); });
			li.addEventListener('mouseleave', function () { li.classList.remove('is-dismissed'); if (!li.classList.contains('is-open')) { setExpanded(li, false); } });
			li.addEventListener('focusin', function () { if (!li.classList.contains('is-dismissed')) { setExpanded(li, true); } });
			li.addEventListener('focusout', function (e) {
				if (!li.contains(e.relatedTarget)) { li.classList.remove('is-open', 'is-dismissed'); setExpanded(li, false); }
			});

			// Touch: first tap opens the submenu, a second tap follows the link.
			var a = link(li);
			if (a) {
				a.addEventListener('click', function (e) {
					if (lastPointer === 'touch' && !li.classList.contains('is-open')) {
						e.preventDefault();
						closeAll(li);
						li.classList.add('is-open');
						setExpanded(li, true);
					}
				});
			}
		});

		document.addEventListener('click', function (e) {
			if (!e.target.closest('.vd-catnav__list > .menu-item-has-children')) { closeAll(null); }
		});

		document.addEventListener('keydown', function (e) {
			if (e.key !== 'Escape') { return; }
			var li = document.activeElement && document.activeElement.closest('.vd-catnav__list > .menu-item-has-children');
			if (li) {
				li.classList.remove('is-open');
				li.classList.add('is-dismissed');
				setExpanded(li, false);
				var a = link(li);
				if (a) { a.focus(); }
			}
		});
	})();

	/* Search category dropdown (ARIA listbox) --------------------------- */
	document.querySelectorAll('[data-vd-catselect]').forEach(function (root) {
		var select = root.querySelector('select');
		var button = root.querySelector('.vd-catselect__btn');
		var valueEl = root.querySelector('.vd-catselect__value');
		var list = root.querySelector('[role="listbox"]');
		var options = Array.prototype.slice.call(root.querySelectorAll('[role="option"]'));
		var active = -1;
		var typed = '';
		var typedTimer;

		if (!select || !button || !list || !options.length) { return; }

		function selectedIndex() {
			for (var i = 0; i < options.length; i++) {
				if (options[i].getAttribute('aria-selected') === 'true') { return i; }
			}
			return 0;
		}

		function setActive(i) {
			if (active > -1) { options[active].classList.remove('is-active'); }
			active = Math.max(0, Math.min(options.length - 1, i));
			var opt = options[active];
			opt.classList.add('is-active');
			list.setAttribute('aria-activedescendant', opt.id);
			// Keep the active option in view without scrolling the page.
			var top = opt.offsetTop;
			var bottom = top + opt.offsetHeight;
			if (top < list.scrollTop) { list.scrollTop = top; }
			else if (bottom > list.scrollTop + list.clientHeight) { list.scrollTop = bottom - list.clientHeight; }
		}

		function open() {
			if (root.classList.contains('is-open')) { return; }
			root.classList.add('is-open');
			button.setAttribute('aria-expanded', 'true');
			setActive(selectedIndex());
			list.focus({ preventScroll: true });
		}

		function close(focusButton) {
			if (!root.classList.contains('is-open')) { return; }
			root.classList.remove('is-open');
			button.setAttribute('aria-expanded', 'false');
			list.removeAttribute('aria-activedescendant');
			if (active > -1) { options[active].classList.remove('is-active'); active = -1; }
			if (focusButton) { button.focus(); }
		}

		function choose(i) {
			options.forEach(function (o) { o.setAttribute('aria-selected', 'false'); });
			var opt = options[i];
			opt.setAttribute('aria-selected', 'true');
			select.value = opt.getAttribute('data-value');
			valueEl.textContent = opt.getAttribute('data-label');
			select.dispatchEvent(new Event('change', { bubbles: true }));
			close(true);
		}

		button.addEventListener('click', function () {
			if (root.classList.contains('is-open')) { close(false); } else { open(); }
		});

		button.addEventListener('keydown', function (e) {
			if (['ArrowDown', 'ArrowUp', 'Enter', ' '].indexOf(e.key) > -1) {
				e.preventDefault();
				open();
			}
		});

		list.addEventListener('keydown', function (e) {
			switch (e.key) {
				case 'ArrowDown': e.preventDefault(); setActive(active + 1); break;
				case 'ArrowUp': e.preventDefault(); setActive(active - 1); break;
				case 'Home': e.preventDefault(); setActive(0); break;
				case 'End': e.preventDefault(); setActive(options.length - 1); break;
				case 'Enter':
				case ' ': e.preventDefault(); choose(active); break;
				case 'Escape': e.preventDefault(); close(true); break;
				case 'Tab': close(false); break;
				default:
					// Type-ahead: jump to the first option starting with the typed letters.
					if (e.key.length === 1 && /\S/.test(e.key)) {
						clearTimeout(typedTimer);
						typed += e.key.toLowerCase();
						typedTimer = setTimeout(function () { typed = ''; }, 600);
						for (var i = 0; i < options.length; i++) {
							if (options[i].getAttribute('data-label').toLowerCase().indexOf(typed) === 0) { setActive(i); break; }
						}
					}
			}
		});

		options.forEach(function (opt, i) {
			opt.addEventListener('mousemove', function () { if (active !== i) { setActive(i); } });
			opt.addEventListener('click', function () { choose(i); });
		});

		// Keep focus on the listbox while clicking inside the widget, so blur doesn't close it early.
		root.querySelector('.vd-catselect__panel').addEventListener('mousedown', function (e) { e.preventDefault(); });
		button.addEventListener('mousedown', function (e) {
			if (root.classList.contains('is-open')) { e.preventDefault(); }
		});

		document.addEventListener('click', function (e) {
			if (!root.contains(e.target)) { close(false); }
		});
		list.addEventListener('blur', function (e) {
			if (!root.contains(e.relatedTarget)) { close(false); }
		});
	});

	/* Quantity +/- buttons ---------------------------------------------- */
	function svg(path) {
		return '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true">' + path + '</svg>';
	}

	function initQuantity(root) {
		(root || document).querySelectorAll('.quantity').forEach(function (wrap) {
			var input = wrap.querySelector('input.qty');
			if (!input || input.type === 'hidden' || wrap.dataset.vdQty) { return; }
			wrap.dataset.vdQty = '1';

			var minus = document.createElement('button');
			minus.type = 'button';
			minus.className = 'vd-qty-btn';
			minus.dataset.step = '-1';
			minus.setAttribute('aria-label', i18n.decrease || '-');
			minus.innerHTML = svg('<path d="M5 12h14"/>');

			var plus = minus.cloneNode(false);
			plus.dataset.step = '1';
			plus.setAttribute('aria-label', i18n.increase || '+');
			plus.innerHTML = svg('<path d="M5 12h14"/><path d="M12 5v14"/>');

			wrap.insertBefore(minus, input);
			wrap.appendChild(plus);
		});
	}

	document.addEventListener('click', function (e) {
		var btn = e.target.closest('.vd-qty-btn');
		if (!btn) { return; }
		var input = btn.parentNode.querySelector('input.qty');
		if (!input) { return; }

		var step = parseFloat(input.step) || 1;
		var min = input.min !== '' ? parseFloat(input.min) : 0;
		var max = input.max !== '' ? parseFloat(input.max) : Infinity;
		var value = parseFloat(input.value) || 0;
		var next = value + step * parseFloat(btn.dataset.step);

		next = Math.min(max, Math.max(min, next));
		if (next === value) { return; }
		input.value = Number.isInteger(step) ? String(Math.round(next)) : next.toFixed(2);
		input.dispatchEvent(new Event('input', { bubbles: true }));
		input.dispatchEvent(new Event('change', { bubbles: true }));
	});

	initQuantity();
	// Classic cart replaces its markup via AJAX; WooCommerce fires these jQuery events.
	if (window.jQuery) {
		window.jQuery(document.body).on('updated_wc_div updated_cart_totals wc_fragments_refreshed', function () {
			initQuantity();
		});
	}

	/* Flash sale countdown ---------------------------------------------- */
	function pad(n) { return n < 10 ? '0' + n : String(n); }

	document.querySelectorAll('[data-vd-countdown]').forEach(function (el) {
		var end = parseInt(el.getAttribute('data-vd-countdown'), 10) * 1000;
		var boxes = {
			h: el.querySelector('[data-unit="h"]'),
			m: el.querySelector('[data-unit="m"]'),
			s: el.querySelector('[data-unit="s"]')
		};
		var timer;

		function tick() {
			var diff = Math.max(0, Math.floor((end - Date.now()) / 1000));
			var h = Math.floor(diff / 3600);
			boxes.h.textContent = pad(h);
			boxes.m.textContent = pad(Math.floor((diff % 3600) / 60));
			boxes.s.textContent = pad(diff % 60);
			if (diff <= 0) {
				clearInterval(timer);
				el.classList.add('is-ended');
				var label = el.querySelector('.vd-countdown__label');
				if (label) { label.textContent = i18n.ended || ''; }
			}
		}
		tick();
		timer = setInterval(tick, 1000);
	});

	/* Horizontal product scrollers -------------------------------------- */
	document.querySelectorAll('[data-vd-scroller]').forEach(function (scroller) {
		var track = scroller.querySelector('ul.products');
		if (!track) { return; }

		function update() {
			var max = track.scrollWidth - track.clientWidth - 2;
			scroller.classList.toggle('can-prev', track.scrollLeft > 2);
			scroller.classList.toggle('can-next', track.scrollLeft < max);
		}

		scroller.querySelectorAll('.vd-scroller__btn').forEach(function (btn) {
			btn.addEventListener('click', function () {
				track.scrollBy({ left: track.clientWidth * 0.85 * parseInt(btn.dataset.dir, 10), behavior: 'smooth' });
			});
		});
		track.addEventListener('scroll', update, { passive: true });
		window.addEventListener('resize', update);
		update();
	});

	/* Banner slider ----------------------------------------------------- */
	// Native scroll-snap does the swiping; JS adds arrows, dots, keyboard and
	// an autoplay that pauses on hover/focus, stops for good after any user
	// interaction, and never runs with prefers-reduced-motion.
	document.querySelectorAll('[data-vd-slider]').forEach(function (slider) {
		var track = slider.querySelector('.vd-slider__track');
		var slides = track ? Array.prototype.slice.call(track.children) : [];
		if (slides.length < 2) { return; }

		var dots = Array.prototype.slice.call(slider.querySelectorAll('.vd-slider__dot'));
		var toggle = slider.querySelector('.vd-slider__toggle');
		var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
		var delay = (parseInt(slider.getAttribute('data-autoplay'), 10) || 0) * 1000;
		var current = 0;
		var timer = null;
		var paused = false;
		var stopped = !delay || reduce;
		var scrollTimer;

		function setCurrent(i) {
			current = i;
			slides.forEach(function (slide, n) {
				var on = n === i;
				slide.setAttribute('aria-hidden', on ? 'false' : 'true');
				slide.querySelectorAll('a, button').forEach(function (el) {
					if (on) { el.removeAttribute('tabindex'); } else { el.setAttribute('tabindex', '-1'); }
				});
			});
			dots.forEach(function (dot, n) {
				if (n === i) { dot.setAttribute('aria-current', 'true'); } else { dot.removeAttribute('aria-current'); }
			});
		}

		function goTo(i) {
			i = (i + slides.length) % slides.length;
			track.scrollTo({ left: i * track.clientWidth, behavior: reduce ? 'auto' : 'smooth' });
			setCurrent(i);
		}

		function tick() {
			if (!paused && !document.hidden) { goTo(current + 1); }
		}

		function play() {
			clearInterval(timer);
			if (!stopped) { timer = setInterval(tick, delay); }
			syncToggle();
		}

		function stop() {
			stopped = true;
			clearInterval(timer);
			syncToggle();
		}

		function syncToggle() {
			if (!toggle) { return; }
			toggle.classList.toggle('is-paused', stopped);
			toggle.setAttribute('aria-label', toggle.getAttribute(stopped ? 'data-label-play' : 'data-label-pause'));
		}

		slider.querySelectorAll('.vd-slider__arrow').forEach(function (btn) {
			btn.addEventListener('click', function () {
				stop();
				goTo(current + parseInt(btn.getAttribute('data-dir'), 10));
			});
		});
		dots.forEach(function (dot, n) {
			dot.addEventListener('click', function () { stop(); goTo(n); });
		});
		if (toggle) {
			if (reduce) { toggle.hidden = true; }
			toggle.addEventListener('click', function () {
				if (stopped) { stopped = false; play(); } else { stop(); }
			});
		}

		// Swiping or dragging the track counts as taking control.
		track.addEventListener('pointerdown', stop, { passive: true });
		track.addEventListener('wheel', function (e) { if (Math.abs(e.deltaX) > Math.abs(e.deltaY)) { stop(); } }, { passive: true });

		slider.addEventListener('keydown', function (e) {
			if (e.key !== 'ArrowLeft' && e.key !== 'ArrowRight') { return; }
			e.preventDefault();
			stop();
			goTo(current + (e.key === 'ArrowRight' ? 1 : -1));
		});

		slider.addEventListener('mouseenter', function () { paused = true; });
		slider.addEventListener('mouseleave', function () { paused = false; });
		slider.addEventListener('focusin', function () { paused = true; });
		slider.addEventListener('focusout', function (e) { if (!slider.contains(e.relatedTarget)) { paused = false; } });

		// Keep dots in sync with swipes.
		track.addEventListener('scroll', function () {
			clearTimeout(scrollTimer);
			scrollTimer = setTimeout(function () {
				var i = Math.round(track.scrollLeft / track.clientWidth);
				if (i !== current && slides[i]) { setCurrent(i); }
			}, 80);
		}, { passive: true });

		window.addEventListener('resize', function () {
			track.scrollTo({ left: current * track.clientWidth, behavior: 'auto' });
		});

		setCurrent(0);
		play();
	});

	/* Reveal on scroll -------------------------------------------------- */
	var reveals = document.querySelectorAll('.vd-reveal');
	if ('IntersectionObserver' in window) {
		var io = new IntersectionObserver(function (entries) {
			entries.forEach(function (entry) {
				if (entry.isIntersecting) {
					entry.target.classList.add('is-visible');
					io.unobserve(entry.target);
				}
			});
		}, { rootMargin: '0px 0px -40px 0px', threshold: 0.05 });
		reveals.forEach(function (el) { io.observe(el); });
	} else {
		reveals.forEach(function (el) { el.classList.add('is-visible'); });
	}
})();
