/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you require will output into a single css file (app.css in this case)
require('../sass/main.scss');
require('../css/app.css');

// Require JS libraries.
const { CountUp } = require('countup.js');
// const MicroModal = require('micromodal');
import MicroModal from 'micromodal';

// import CountUp from 'countup.js';

// Need jQuery? Install it with "yarn add jquery", then uncomment to require it.
const $ = require('jquery');
global.$ = global.jQuery = $;

(function() {

	"use strict";

	// Define an async function
	async function fetchAsync (endpoint) {
		let uri = '/';
		uri += endpoint;
		let response = await fetch(uri, {
			method:'GET'
		});
		let data = await response.json();
		return data;
	}

	// Define countUp function.
	function countUp(e, start, end, decimalPlaces) {
		const options = {
			  startVal: start,
			  decimalPlaces: decimalPlaces,
		};

		var countUp = new CountUp(e, end, options);
		if (!countUp.error) {
			countUp.start();
		} else {
			  console.error(countUp.error);
		}
	}


	// Initialize MicroModal.
	MicroModal.init();


	var	$body = document.querySelector('body');
	var $loadingParagraph = document.getElementById('loading-p');

	// Methods/polyfills.

	// classList | (c) @remy | github.com/remy/polyfills | rem.mit-license.org
	!function(){function t(t){this.el=t;for(var n=t.className.replace(/^\s+|\s+$/g,"").split(/\s+/),i=0;i<n.length;i++)e.call(this,n[i])}function n(t,n,i){Object.defineProperty?Object.defineProperty(t,n,{get:i}):t.__defineGetter__(n,i)}if(!("undefined"==typeof window.Element||"classList"in document.documentElement)){var i=Array.prototype,e=i.push,s=i.splice,o=i.join;t.prototype={add:function(t){this.contains(t)||(e.call(this,t),this.el.className=this.toString())},contains:function(t){return-1!=this.el.className.indexOf(t)},item:function(t){return this[t]||null},remove:function(t){if(this.contains(t)){for(var n=0;n<this.length&&this[n]!=t;n++);s.call(this,n,1),this.el.className=this.toString()}},toString:function(){return o.call(this," ")},toggle:function(t){return this.contains(t)?this.remove(t):this.add(t),this.contains(t)}},window.DOMTokenList=t,n(Element.prototype,"classList",function(){return new t(this)})}}();

	// canUse
	window.canUse=function(p){if(!window._canUse)window._canUse=document.createElement("div");var e=window._canUse.style,up=p.charAt(0).toUpperCase()+p.slice(1);return p in e||"Moz"+up in e||"Webkit"+up in e||"O"+up in e||"ms"+up in e};

	// window.addEventListener
	(function(){if("addEventListener"in window)return;window.addEventListener=function(type,f){window.attachEvent("on"+type,f)}})();

	// Do stuff after page is fully loaded.
	window.addEventListener('load', function() {
		// Play initial animations on page load.
		window.setTimeout(function() {
			$body.classList.remove('is-preload');
		}, 100);



		// Get and display Average Price.
		let avPriceElement = document.getElementById('average-price');
		let adsQtyElement = document.getElementById('ads-qty');
		fetchAsync('api/get_average_price')
		.then(function(data) {
			// Remove loading dots.
			$loadingParagraph.classList.remove('loading-dots');
			
			// Count up from 0 and display value.
			countUp(avPriceElement, 0.00, data.average_price, 2);

			// Count down from 100.
			countUp(adsQtyElement, 100, data.total_ads_evaluated, 0);
		})
		.catch(function(error) {
			console.log(error);
		});
	});

	// Slideshow Background.
	// (function() {

	// 		// Settings.
	// 		var settings = {

	// 				// Images (in the format of 'url': 'alignment').
	// 				images: {
	// 					'images/bg01.jpg': 'center',
	// 					'images/bg02.jpg': 'center',
	// 					'images/bg03.jpg': 'center'
	// 				},

	// 				// Delay.
	// 				delay: 6000

	// 			};

	// 		// Vars.
	// 		var	pos = 0, lastPos = 0,
	// 		$wrapper, $bgs = [], $bg,
	// 		k, v;

	// 		// Create BG wrapper, BGs.
	// 		$wrapper = document.createElement('div');
	// 		$wrapper.id = 'bg';
	// 		$body.appendChild($wrapper);

	// 		for (k in settings.images) {

	// 				// Create BG.
	// 				$bg = document.createElement('div');
	// 				$bg.style.backgroundImage = 'url("' + k + '")';
	// 				$bg.style.backgroundPosition = settings.images[k];
	// 				$wrapper.appendChild($bg);

	// 				// Add it to array.
	// 				$bgs.push($bg);

	// 			}

	// 		// Main loop.
	// 		$bgs[pos].classList.add('visible');
	// 		$bgs[pos].classList.add('top');

	// 			// Bail if we only have a single BG or the client doesn't support transitions.
	// 			if ($bgs.length == 1
	// 				||	!canUse('transition'))
	// 				return;

	// 			window.setInterval(function() {

	// 				lastPos = pos;
	// 				pos++;

	// 				// Wrap to beginning if necessary.
	// 				if (pos >= $bgs.length)
	// 					pos = 0;

	// 				// Swap top images.
	// 				$bgs[lastPos].classList.remove('top');
	// 				$bgs[pos].classList.add('visible');
	// 				$bgs[pos].classList.add('top');

	// 				// Hide last image after a short delay.
	// 				window.setTimeout(function() {
	// 					$bgs[lastPos].classList.remove('visible');
	// 				}, settings.delay / 2);

	// 			}, settings.delay);

	// 		})();

	// Signup Form.
	// (function() {

	// 		// Vars.
	// 		var $form = document.querySelectorAll('#signup-form')[0],
	// 		$submit = document.querySelectorAll('#signup-form input[type="submit"]')[0],
	// 		$message;

	// 		// Bail if addEventListener isn't supported.
	// 		if (!('addEventListener' in $form))
	// 			return;

	// 		// Message.
	// 		$message = document.createElement('span');
	// 		$message.classList.add('message');
	// 		$form.appendChild($message);

	// 		$message._show = function(type, text) {

	// 			$message.innerHTML = text;
	// 			$message.classList.add(type);
	// 			$message.classList.add('visible');

	// 			window.setTimeout(function() {
	// 				$message._hide();
	// 			}, 3000);

	// 		};

	// 		$message._hide = function() {
	// 			$message.classList.remove('visible');
	// 		};

	// 		// Events.
	// 		// Note: If you're *not* using AJAX, get rid of this event listener.
	// 		$form.addEventListener('submit', function(event) {

	// 			event.stopPropagation();
	// 			event.preventDefault();

	// 				// Hide message.
	// 				$message._hide();

	// 				// Disable submit.
	// 				$submit.disabled = true;

	// 				// Process form.
	// 				// Note: Doesn't actually do anything yet (other than report back with a "thank you"),
	// 				// but there's enough here to piece together a working AJAX submission call that does.
	// 				window.setTimeout(function() {

	// 						// Reset form.
	// 						$form.reset();

	// 						// Enable submit.
	// 						$submit.disabled = false;

	// 						// Show message.
	// 						$message._show('success', 'Thank you!');
	// 							//$message._show('failure', 'Something went wrong. Please try again.');

	// 						}, 750);

	// 			});

	// 	})();

})();
