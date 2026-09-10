/**
 * Advanced Table of Contents for Elementor - Vanilla JS Engine
 *
 * Performance-focused, WCAG-conscious, IntersectionObserver scroll spy,
 * hierarchical builder, live search, and reading progress calculator.
 *
 * @package AdvancedElementorTOC
 */

(function () {
	'use strict';

	const AdvancedElementorTOC = {
		instances: new Map(),

		init(element) {
			const wrapper = typeof element === 'string' ? document.querySelector(element) : element;
			if (!wrapper) return;

			if (this.instances.has(wrapper)) {
				this.instances.get(wrapper).destroy();
			}

			const instance = new TOCInstance(wrapper);
			this.instances.set(wrapper, instance);
		},

		refresh(element) {
			if (element && this.instances.has(element)) {
				this.instances.get(element).refresh();
			} else {
				this.instances.forEach((inst) => inst.refresh());
			}
		},

		destroy(element) {
			if (element && this.instances.has(element)) {
				this.instances.get(element).destroy();
				this.instances.delete(element);
			} else {
				this.instances.forEach((inst) => inst.destroy());
				this.instances.clear();
			}
		},
	};

	class TOCInstance {
		constructor(wrapper) {
			this.wrapper = wrapper;
			this.config = this.parseConfig();
			this.usedSlugs = new Set();
			this.headingElements = [];
			this.observer = null;
			this.scrollTimeout = null;
			this.rafId = null;
			this.activeHeadingId = null;

			this.init();
		}

		parseConfig() {
			try {
				const raw = this.wrapper.getAttribute('data-aetoc-config');
				return raw ? JSON.parse(raw) : {};
			} catch (e) {
				console.error('[Advanced TOC] Config parse error:', e);
				return {};
			}
		}

		init() {
			this.render();
			this.setupCollapsible();
			this.setupSearch();
			this.setupSmoothScroll();
			this.setupScrollSpy();
			this.setupReadingProgress();
			this.setupFloatingAndDrawer();
			this.setupBackToTop();
			this.handleHashOnLoad();

			if (this.config.isEditor) {
				this.setupEditorObserver();
			}
		}

		refresh() {
			this.destroy();
			this.config = this.parseConfig();
			this.init();
		}

		destroy() {
			if (this.observer) {
				this.observer.disconnect();
				this.observer = null;
			}
			if (this.rafId) {
				cancelAnimationFrame(this.rafId);
			}
			if (this.mutationObserver) {
				this.mutationObserver.disconnect();
			}
		}

		getHeadings() {
			this.usedSlugs.clear();
			const source = this.config.source || 'entire_page';
			let searchRoot = document.body;

			if (source === 'post_content') {
				searchRoot = document.querySelector('.entry-content, .post-content, article') || document.body;
			} else if (source === 'elementor_content') {
				searchRoot = document.querySelector('.elementor:not(.aetoc-wrapper)') || document.body;
			} else if (source === 'custom_selector' && this.config.customSelector) {
				searchRoot = document.querySelector(this.config.customSelector) || document.body;
			}

			const allowedTags = (this.config.headings || ['h2', 'h3', 'h4']).map((t) => t.toUpperCase());
			const selector = allowedTags.join(', ');

			if (!selector) return [];

			const allHeadings = Array.from(searchRoot.querySelectorAll(selector));

			const excludeTexts = this.parseList(this.config.excludeText);
			const excludeClasses = this.parseList(this.config.excludeClasses).map((c) => c.replace(/^\./, ''));
			const excludeSelectors = this.parseList(this.config.excludeSelectors);

			const filtered = allHeadings.filter((heading) => {
				if (this.wrapper.contains(heading) || heading.closest('.aetoc-wrapper')) {
					return false;
				}

				const level = parseInt(heading.tagName.replace('H', ''), 10);
				if (this.config.maxDepth && level > this.config.maxDepth) {
					return false;
				}

				const text = heading.textContent.trim();
				if (!text) return false;

				if (excludeTexts.length > 0) {
					const lowerText = text.toLowerCase();
					for (const exc of excludeTexts) {
						if (lowerText === exc.toLowerCase() || lowerText.includes(exc.toLowerCase())) {
							return false;
						}
					}
				}

				if (excludeClasses.length > 0) {
					for (const excClass of excludeClasses) {
						if (heading.classList.contains(excClass)) {
							return false;
						}
					}
				}

				if (excludeSelectors.length > 0) {
					for (const excSel of excludeSelectors) {
						try {
							if (heading.matches(excSel) || heading.closest(excSel)) {
								return false;
							}
						} catch (e) {
							// Ignore invalid selector syntax.
						}
					}
				}

				return true;
			});

			let items = filtered;
			const firstN = Math.max(0, this.config.excludeFirstN || 0);
			const lastN = Math.max(0, this.config.excludeLastN || 0);

			if (firstN > 0 && firstN < items.length) {
				items = items.slice(firstN);
			}
			if (lastN > 0 && lastN < items.length) {
				items = items.slice(0, items.length - lastN);
			}

			const headingsData = [];
			items.forEach((heading) => {
				let id = heading.getAttribute('id');
				if (this.config.forceRegenerateIds || !id) {
					id = this.generateUniqueSlug(heading.textContent);
					heading.setAttribute('id', id);
				} else {
					this.usedSlugs.add(id);
				}

				const level = parseInt(heading.tagName.replace('H', ''), 10);
				headingsData.push({
					id: id,
					text: heading.textContent.trim(),
					level: level,
					tag: heading.tagName.toLowerCase(),
					element: heading,
					children: [],
				});
			});

			this.headingElements = items;
			return headingsData;
		}

		generateUniqueSlug(text) {
			let slug = text
				.trim()
				.toLowerCase()
				.replace(/&/g, ' and ')
				.replace(/[\s\W-]+/g, '-')
				.replace(/^-+|-+$/g, '');

			if (!slug) {
				slug = 'section';
			}

			let uniqueSlug = slug;
			let counter = 2;
			while (this.usedSlugs.has(uniqueSlug) || document.getElementById(uniqueSlug)) {
				uniqueSlug = `${slug}-${counter}`;
				counter++;
			}

			this.usedSlugs.add(uniqueSlug);
			return uniqueSlug;
		}

		parseList(str) {
			if (!str || typeof str !== 'string') return [];
			return str
				.split(/[\r\n,]+/)
				.map((s) => s.trim())
				.filter(Boolean);
		}

		buildHierarchy(items) {
			if (!items.length) return [];

			const root = [];
			const stack = [];
			const counters = [0, 0, 0, 0, 0, 0, 0];

			items.forEach((item) => {
				const level = item.level;

				while (stack.length && stack[stack.length - 1].level >= level) {
					stack.pop();
				}

				for (let i = level + 1; i <= 6; i++) {
					counters[i] = 0;
				}
				counters[level]++;

				item.number = this.formatNumber(counters, level);

				if (!stack.length) {
					root.push(item);
					stack.push(item);
				} else {
					const parent = stack[stack.length - 1];
					parent.children.push(item);
					stack.push(item);
				}
			});

			return root;
		}

		formatNumber(counters, currentLevel) {
			const style = this.config.numberingStyle || 'numeric';
			const prefix = this.config.numberingPrefix ? `${this.config.numberingPrefix} ` : '';
			const sep = this.config.numberingSeparator || '.';

			if (style === 'none') return '';

			let formatted = '';
			switch (style) {
				case 'hierarchical':
					const parts = [];
					for (let l = 1; l <= currentLevel; l++) {
						if (counters[l] > 0) parts.push(counters[l]);
					}
					formatted = parts.join(sep);
					break;

				case 'alphabetical':
					formatted = this.numberToAlpha(counters[currentLevel]);
					break;

				case 'roman':
					formatted = this.numberToRoman(counters[currentLevel]);
					break;

				case 'custom':
					formatted = counters[currentLevel].toString();
					break;

				case 'numeric':
				default:
					formatted = counters[currentLevel].toString();
					break;
			}

			return `${prefix}${formatted}`;
		}

		numberToAlpha(num) {
			let alpha = '';
			while (num > 0) {
				const rem = (num - 1) % 26;
				alpha = String.fromCharCode(65 + rem) + alpha;
				num = Math.floor((num - rem) / 26);
			}
			return alpha || 'A';
		}

		numberToRoman(num) {
			const map = [
				['M', 1000],
				['CM', 900],
				['D', 500],
				['CD', 400],
				['C', 100],
				['XC', 90],
				['L', 50],
				['XL', 40],
				['X', 10],
				['IX', 9],
				['V', 5],
				['IV', 4],
				['I', 1],
			];
			let res = '';
			for (const [roman, val] of map) {
				while (num >= val) {
					res += roman;
					num -= val;
				}
			}
			return res || 'I';
		}

		render() {
			const container = this.wrapper.querySelector('.aetoc-content-container');
			if (!container) return;

			const rawHeadings = this.getHeadings();

			const countBadge = this.wrapper.querySelector('.aetoc-count-badge');
			if (countBadge) {
				const count = rawHeadings.length;
				countBadge.textContent = count;
				const format = count === 1 ? this.config.countFormatSingular : this.config.countFormatPlural;
				if (format && format.includes('{count}')) {
					countBadge.textContent = format.replace('{count}', count);
				}
			}

			if (rawHeadings.length === 0) {
				const emptyMsg = this.config.isEditor
					? (window.aetocData?.i18n?.noHeadingsEditor || 'No headings found. Add H2-H6 headings to your page to generate the Table of Contents.')
					: (window.aetocData?.i18n?.noHeadings || 'No headings found.');
				container.innerHTML = `<div class="aetoc-empty-state"><p>${emptyMsg}</p></div>`;
				return;
			}

			const hierarchy = this.config.layout === 'flat' ? rawHeadings : this.buildHierarchy(rawHeadings);
			const treeHtml = this.renderList(hierarchy, 1, true);

			container.innerHTML = treeHtml;

			this.setupMaxItems();
		}

		renderList(items, level = 1, isRoot = false) {
			if (!items || !items.length) return '';

			const rootClass = isRoot ? 'aetoc-list aetoc-list-root' : `aetoc-list aetoc-sublist aetoc-sublist-level-${level}`;
			const collapsedAttr = this.config.collapsibleSubitems && this.config.subitemsInitCollapsed && !isRoot ? 'style="display:none;" aria-hidden="true"' : '';

			let html = `<ul class="${rootClass}" role="list" ${collapsedAttr}>`;

			items.forEach((item) => {
				const hasChildren = item.children && item.children.length > 0;
				const numSpan = item.number ? `<span class="aetoc-number">${item.number}</span>` : '';
				const levelClass = `aetoc-level-${item.level}`;

				let subToggle = '';
				if (hasChildren && this.config.collapsibleSubitems) {
					subToggle = `<button type="button" class="aetoc-sub-toggle" aria-expanded="${this.config.subitemsInitCollapsed ? 'false' : 'true'}" aria-label="Toggle Sub-items"><span class="aetoc-sub-toggle-icon">▸</span></button>`;
				}

				html += `
					<li class="aetoc-item ${levelClass} ${hasChildren ? 'aetoc-has-children' : ''}" data-heading-id="${item.id}">
						<div class="aetoc-link-wrap">
							<a href="#${item.id}" class="aetoc-link" data-target-id="${item.id}">
								${numSpan}
								<span class="aetoc-text">${item.text}</span>
							</a>
							${subToggle}
						</div>
						${hasChildren ? this.renderList(item.children, level + 1, false) : ''}
					</li>
				`;
			});

			html += '</ul>';
			return html;
		}

		setupMaxItems() {
			const maxItems = this.config.maxItems || 0;
			if (maxItems <= 0) return;

			const rootList = this.wrapper.querySelector('.aetoc-list-root');
			if (!rootList) return;

			const directItems = Array.from(rootList.children);
			if (directItems.length <= maxItems) return;

			directItems.forEach((item, index) => {
				if (index >= maxItems) {
					item.classList.add('aetoc-item-hidden');
					item.style.display = 'none';
				}
			});

			const btnWrap = document.createElement('div');
			btnWrap.className = 'aetoc-view-more-wrap';
			btnWrap.innerHTML = `<button type="button" class="aetoc-view-more-btn" aria-expanded="false">${this.config.viewMoreText || 'View More'}</button>`;
			rootList.parentNode.appendChild(btnWrap);

			const btn = btnWrap.querySelector('.aetoc-view-more-btn');
			let expanded = false;
			btn.addEventListener('click', () => {
				expanded = !expanded;
				btn.setAttribute('aria-expanded', expanded.toString());
				btn.textContent = expanded ? (this.config.viewLessText || 'View Less') : (this.config.viewMoreText || 'View More');

				directItems.forEach((item, index) => {
					if (index >= maxItems) {
						item.style.display = expanded ? '' : 'none';
					}
				});
			});
		}

		setupCollapsible() {
			if (!this.config.collapsible) return;

			const toggleBtn = this.wrapper.querySelector('.aetoc-toggle-btn');
			const body = this.wrapper.querySelector('.aetoc-body');
			if (!toggleBtn || !body) return;

			toggleBtn.addEventListener('click', () => {
				const isExpanded = toggleBtn.getAttribute('aria-expanded') === 'true';
				const nextState = !isExpanded;

				toggleBtn.setAttribute('aria-expanded', nextState.toString());

				const expandIcon = toggleBtn.querySelector('.aetoc-icon-expand');
				const collapseIcon = toggleBtn.querySelector('.aetoc-icon-collapse');

				if (expandIcon && collapseIcon) {
					expandIcon.style.display = nextState ? 'none' : '';
					collapseIcon.style.display = nextState ? '' : 'none';
				}

				if (nextState) {
					body.style.display = '';
					body.style.maxHeight = '0px';
					body.style.overflow = 'hidden';
					body.style.transition = `max-height ${this.config.animationSpeed || 300}ms ease`;
					requestAnimationFrame(() => {
						body.style.maxHeight = `${body.scrollHeight}px`;
						setTimeout(() => {
							body.style.maxHeight = '';
							body.style.overflow = '';
						}, this.config.animationSpeed || 300);
					});
				} else {
					body.style.maxHeight = `${body.scrollHeight}px`;
					body.style.overflow = 'hidden';
					body.style.transition = `max-height ${this.config.animationSpeed || 300}ms ease`;
					requestAnimationFrame(() => {
						body.style.maxHeight = '0px';
						setTimeout(() => {
							body.style.display = 'none';
							body.style.maxHeight = '';
							body.style.overflow = '';
						}, this.config.animationSpeed || 300);
					});
				}
			});

			if (this.config.collapsibleSubitems) {
				this.wrapper.querySelectorAll('.aetoc-sub-toggle').forEach((btn) => {
					btn.addEventListener('click', (e) => {
						e.stopPropagation();
						const subList = btn.closest('.aetoc-item').querySelector('.aetoc-sublist');
						if (!subList) return;

						const isExp = btn.getAttribute('aria-expanded') === 'true';
						btn.setAttribute('aria-expanded', (!isExp).toString());
						btn.classList.toggle('aetoc-expanded', !isExp);
						subList.style.display = isExp ? 'none' : '';
						subList.setAttribute('aria-hidden', isExp ? 'true' : 'false');
					});
				});
			}
		}

		setupSearch() {
			if (!this.config.enableSearch) return;

			const input = this.wrapper.querySelector('.aetoc-search-input');
			if (!input) return;

			input.addEventListener('input', () => {
				const query = input.value.trim().toLowerCase();
				const items = this.wrapper.querySelectorAll('.aetoc-item');

				if (!query) {
					items.forEach((item) => {
						item.style.display = '';
					});
					return;
				}

				items.forEach((item) => {
					const textSpan = item.querySelector('.aetoc-text');
					const text = textSpan ? textSpan.textContent.toLowerCase() : '';
					const matches = text.includes(query);

					if (matches) {
						item.style.display = '';
						let parent = item.parentElement.closest('.aetoc-item');
						while (parent) {
							parent.style.display = '';
							const sub = parent.querySelector('.aetoc-sublist');
							if (sub) {
								sub.style.display = '';
							}
							parent = parent.parentElement.closest('.aetoc-item');
						}
					} else {
						const childMatches = Array.from(item.querySelectorAll('.aetoc-text')).some((span) =>
							span.textContent.toLowerCase().includes(query)
						);
						item.style.display = childMatches ? '' : 'none';
					}
				});
			});
		}

		setupSmoothScroll() {
			this.wrapper.addEventListener('click', (e) => {
				const link = e.target.closest('.aetoc-link');
				if (!link) return;

				const targetId = link.getAttribute('data-target-id');
				const targetEl = document.getElementById(targetId);
				if (!targetEl) return;

				e.preventDefault();

				const offset = this.calculateScrollOffset();
				const targetPos = targetEl.getBoundingClientRect().top + window.pageYOffset - offset;

				window.scrollTo({
					top: Math.max(0, targetPos),
					behavior: this.config.smoothScroll ? 'smooth' : 'auto',
				});

				targetEl.setAttribute('tabindex', '-1');
				targetEl.focus({ preventScroll: true });

				if (this.config.updateUrlHash) {
					this.updateHash(targetId);
				}
			});
		}

		calculateScrollOffset() {
			let totalOffset = this.config.scrollOffset || 90;

			const adminBar = document.getElementById('wpadminbar');
			if (adminBar && getComputedStyle(adminBar).position === 'fixed') {
				totalOffset += adminBar.offsetHeight;
			}

			return totalOffset;
		}

		updateHash(targetId) {
			const hash = `#${targetId}`;
			const mode = this.config.historyMode || 'replace_state';

			if (mode === 'replace_state' && window.history.replaceState) {
				window.history.replaceState(null, '', hash);
			} else if (mode === 'push_state' && window.history.pushState) {
				window.history.pushState(null, '', hash);
			}
		}

		setupScrollSpy() {
			const mode = this.config.scrollSpyMode || 'intersection_observer';
			if (mode === 'disabled' || !this.headingElements.length) return;

			if (mode === 'intersection_observer' && 'IntersectionObserver' in window) {
				const rootMargin = this.config.observerRootMargin || '-100px 0px -65% 0px';
				this.observer = new IntersectionObserver(
					(entries) => {
						entries.forEach((entry) => {
							if (entry.isIntersecting) {
								this.setActiveHeading(entry.target.id);
							}
						});
					},
					{
						rootMargin: rootMargin,
						threshold: 0.1,
					}
				);

				this.headingElements.forEach((el) => {
					this.observer.observe(el);
				});
			} else {
				window.addEventListener('scroll', () => {
					if (this.scrollTimeout) return;
					this.scrollTimeout = setTimeout(() => {
						this.scrollTimeout = null;
						this.checkActiveOnScroll();
					}, 100);
				});
			}
		}

		checkActiveOnScroll() {
			const offset = this.calculateScrollOffset() + 20;
			let currentId = null;

			for (let i = 0; i < this.headingElements.length; i++) {
				const heading = this.headingElements[i];
				const rect = heading.getBoundingClientRect();
				if (rect.top <= offset) {
					currentId = heading.id;
				} else {
					break;
				}
			}

			if (currentId) {
				this.setActiveHeading(currentId);
			}
		}

		setActiveHeading(id) {
			if (!id || this.activeHeadingId === id) return;
			this.activeHeadingId = id;

			this.wrapper.querySelectorAll('.aetoc-item.aetoc-active').forEach((el) => {
				el.classList.remove('aetoc-active');
			});

			const activeItem = this.wrapper.querySelector(`.aetoc-item[data-heading-id="${id}"]`);
			if (!activeItem) return;

			let current = activeItem;
			while (current && current.classList.contains('aetoc-item')) {
				current.classList.add('aetoc-active');
				const parentSublist = current.closest('.aetoc-sublist');
				if (parentSublist) {
					if (this.config.collapsibleSubitems) {
						parentSublist.style.display = '';
						parentSublist.setAttribute('aria-hidden', 'false');
					}
					current = parentSublist.closest('.aetoc-item');
				} else {
					break;
				}
			}
		}

		setupReadingProgress() {
			if (!this.config.showProgress) return;

			const barFill = this.wrapper.querySelector('.aetoc-progress-bar-fill');
			const circleFill = this.wrapper.querySelector('.aetoc-progress-circle-fill');
			const circleText = this.wrapper.querySelector('.aetoc-circular-percent');
			const progressText = this.wrapper.querySelector('.aetoc-progress-text');

			const updateProgress = () => {
				const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
				const docHeight = document.documentElement.scrollHeight - document.documentElement.clientHeight;
				const percent = docHeight > 0 ? Math.min(100, Math.max(0, Math.round((scrollTop / docHeight) * 100))) : 0;

				if (barFill) {
					barFill.style.width = `${percent}%`;
				}
				if (circleFill) {
					circleFill.setAttribute('stroke-dasharray', `${percent}, 100`);
				}
				if (circleText) {
					circleText.textContent = `${percent}%`;
				}
				if (progressText) {
					progressText.textContent = `${percent}%`;
				}
			};

			window.addEventListener('scroll', () => {
				if (!this.rafId) {
					this.rafId = requestAnimationFrame(() => {
						updateProgress();
						this.rafId = null;
					});
				}
			});

			updateProgress();
		}

		setupFloatingAndDrawer() {
			const closeBtn = this.wrapper.querySelector('.aetoc-floating-close-btn');
			if (closeBtn) {
				closeBtn.addEventListener('click', () => {
					this.wrapper.classList.toggle('aetoc-minimized');
				});
			}
		}

		setupBackToTop() {
			const btt = this.wrapper.querySelector('.aetoc-back-to-top');
			if (!btt) return;

			btt.addEventListener('click', (e) => {
				e.preventDefault();
				window.scrollTo({
					top: 0,
					behavior: 'smooth',
				});
			});
		}

		handleHashOnLoad() {
			if (!this.config.scrollToHashOnLoad) return;

			const hash = window.location.hash.replace('#', '');
			if (!hash) return;

			setTimeout(() => {
				const target = document.getElementById(hash);
				if (target) {
					const offset = this.calculateScrollOffset();
					const targetPos = target.getBoundingClientRect().top + window.pageYOffset - offset;
					window.scrollTo({
						top: Math.max(0, targetPos),
						behavior: 'smooth',
					});
					this.setActiveHeading(hash);
				}
			}, 300);
		}

		setupEditorObserver() {
			const editorContent = document.querySelector('.elementor');
			if (!editorContent) return;

			let debounceTimer;
			this.mutationObserver = new MutationObserver(() => {
				clearTimeout(debounceTimer);
				debounceTimer = setTimeout(() => {
					this.render();
				}, 400);
			});

			this.mutationObserver.observe(editorContent, {
				childList: true,
				subtree: true,
				characterData: true,
			});
		}
	}

	window.AdvancedElementorTOC = AdvancedElementorTOC;

	if (typeof window !== 'undefined') {
		const initHandler = () => {
			if (window.elementorFrontend && window.elementorFrontend.hooks) {
				window.elementorFrontend.hooks.addAction(
					'frontend/element_ready/advanced-elementor-toc.default',
					($scope) => {
						const el = $scope[0] ? $scope[0].querySelector('.aetoc-wrapper') : null;
						if (el) {
							AdvancedElementorTOC.init(el);
						}
					}
				);
			} else {
				document.querySelectorAll('.aetoc-wrapper').forEach((el) => {
					AdvancedElementorTOC.init(el);
				});
			}
		};

		if (document.readyState === 'loading') {
			document.addEventListener('DOMContentLoaded', initHandler);
		} else {
			initHandler();
		}
	}
})();
