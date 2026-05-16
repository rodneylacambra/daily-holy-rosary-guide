/**
 * Holy Rosary - Audio Mode
 *
 * Text-to-speech using the Web Speech API.
 * Loaded only when audio is enabled in plugin settings.
 *
 * Features:
 *  - Play / Pause per step
 *  - Auto-advance: moves to next step when speech ends
 *  - Speed control: 0.75x / 1x / 1.25x / 1.5x
 *  - Graceful fallback when Web Speech API is unavailable
 *
 * @package HolyRosary
 */

/* global HolyRosary, holyRosaryData */
( function () {
	'use strict';

	// ── Bail if Web Speech API is not supported ──────────────────────────────
	if ( ! ( 'speechSynthesis' in window ) || ! ( 'SpeechSynthesisUtterance' in window ) ) {
		return;
	}

	const synth = window.speechSynthesis;
	const i18n  = ( typeof holyRosaryData !== 'undefined' && holyRosaryData.i18n ) ? holyRosaryData.i18n : {};

	// ── Per-instance state ───────────────────────────────────────────────────
	const instances = {};

	/**
	 * Build the text to be spoken for a given step.
	 *
	 * Rules:
	 *  1. Welcome step  → read the title only (it is the greeting).
	 *  2. Mystery steps → announce the mystery name + title BEFORE the prayer.
	 *  3. All others    → read prayer text(s) only. No titles, no labels.
	 *
	 * @param {Object}   step           Step object from buildSteps().
	 * @param {Function} getMysteryData Callback returning current mystery object.
	 * @return {string}
	 */
	function buildSpeechText( step, getMysteryData ) {
		if ( ! step ) return '';

		const ORDINALS = [ 'First', 'Second', 'Third', 'Fourth', 'Fifth' ];

		// Rule 1: Welcome step — speak the title as the greeting.
		if ( step.num === 'Welcome' ) {
			return step.title || '';
		}

		const parts    = [];
		const prayers  = step.prayers || [];
		const hasMystery = typeof step.mystery !== 'undefined' && getMysteryData;

		// Build mystery announcement text (used in Rule 2).
		let mysteryAnnouncement = '';
		if ( hasMystery ) {
			const m       = getMysteryData();
			const ordinal = ORDINALS[ step.mystery ] || '';
			const mName   = ( m && m.name ) ? m.name : '';
			const mItem   = ( m && m.list && m.list[ step.mystery ] ) ? m.list[ step.mystery ] : null;
			if ( ordinal && mName ) {
				mysteryAnnouncement += 'The ' + ordinal + ' ' + mName + ' Mystery. ';
			}
			if ( mItem && mItem.title ) {
				mysteryAnnouncement += mItem.title + '.';
			}
		}

		// Rule 2 + 3: Iterate prayers and insert mystery announcement at the
		// correct position:
		//   - Multiple prayers (e.g. Glory Be + Our Father): announce AFTER the
		//     first prayer — "Glory Be... [Mystery]... Our Father..."
		//   - Single prayer (e.g. just Our Father): announce BEFORE it —
		//     "[Mystery]... Our Father..."
		if ( prayers.length > 1 ) {
			prayers.forEach( function ( p, idx ) {
				parts.push( p.t );
				// Inject mystery announcement after the first prayer.
				if ( idx === 0 && mysteryAnnouncement ) {
					parts.push( mysteryAnnouncement );
				}
			} );
		} else {
			// Single prayer — mystery goes first if present.
			if ( mysteryAnnouncement ) {
				parts.push( mysteryAnnouncement );
			}
			prayers.forEach( function ( p ) {
				parts.push( p.t );
			} );
		}

		return parts.join( ' ' );
	}

	/**
	 * Initialise audio controls for a given rosary instance.
	 *
	 * @param {string}   rootId    The DOM element ID of the rosary instance.
	 * @param {Function} getStep   Callback that returns the current step object.
	 * @param {Function} nextStep  Callback to advance to the next step.
	 * @param {Function} getTotal  Callback that returns total step count.
	 * @param {Function} getIndex  Callback that returns current step index.
	 */
	function initAudio( rootId, getStep, nextStep, getTotal, getIndex, getMysteryData ) {
		const root = document.getElementById( rootId );
		if ( ! root ) return;

		// State for this instance.
		const state = {
			playing:     false,
			autoAdvance: false,
			rate:        1,
			utterance:   null,
		};

		instances[ rootId ] = state;

		// ── Build UI ─────────────────────────────────────────────────────────
		const bar = document.createElement( 'div' );
		bar.className   = 'hr-audio-bar';
		bar.id          = rootId + '-audio-bar';
		bar.setAttribute( 'aria-label', 'Audio controls' );

		bar.innerHTML = `
			<div class="hr-audio-left">
				<button class="hr-audio-btn hr-audio-play" id="${ rootId }-audio-play"
					aria-label="Play prayer audio" title="Play">
					<span class="hr-audio-icon-play">▶</span>
					<span class="hr-audio-icon-pause" style="display:none;">⏸</span>
				</button>
				<span class="hr-audio-indicator" id="${ rootId }-audio-indicator" aria-live="polite">
					<span class="hr-audio-dot"></span>
					<span class="hr-audio-dot"></span>
					<span class="hr-audio-dot"></span>
				</span>
			</div>
			<div class="hr-audio-right">
				<label class="hr-audio-auto-label" for="${ rootId }-audio-auto">
					<input type="checkbox" class="hr-audio-auto" id="${ rootId }-audio-auto"
						aria-label="Auto-advance to next step when speech ends" />
					<span>Auto</span>
				</label>
				<select class="hr-audio-speed" id="${ rootId }-audio-speed"
					aria-label="Speech speed">
					<option value="0.75">0.75×</option>
					<option value="1"    selected>1×</option>
					<option value="1.25">1.25×</option>
					<option value="1.5">1.5×</option>
				</select>
			</div>
		`;

		// Insert after the step card.
		const card = document.getElementById( rootId + '-card' );
		if ( card && card.parentNode ) {
			card.parentNode.insertBefore( bar, card.nextSibling );
		} else {
			root.appendChild( bar );
		}

		// ── Element references ────────────────────────────────────────────────
		const playBtn   = document.getElementById( rootId + '-audio-play' );
		const indicator = document.getElementById( rootId + '-audio-indicator' );
		const autoChk   = document.getElementById( rootId + '-audio-auto' );
		const speedSel  = document.getElementById( rootId + '-audio-speed' );

		// ── Helpers ───────────────────────────────────────────────────────────

		function setPlayIcon( isPlaying ) {
			const iconPlay  = playBtn.querySelector( '.hr-audio-icon-play' );
			const iconPause = playBtn.querySelector( '.hr-audio-icon-pause' );
			if ( iconPlay )  iconPlay.style.display  = isPlaying ? 'none'   : 'inline';
			if ( iconPause ) iconPause.style.display = isPlaying ? 'inline' : 'none';
			playBtn.setAttribute( 'aria-label', isPlaying ? 'Pause audio' : 'Play prayer audio' );
			bar.classList.toggle( 'is-playing', isPlaying );
		}

		function setIndicator( active ) {
			indicator.classList.toggle( 'is-active', active );
		}

		function stopSpeech() {
			synth.cancel();
			state.playing   = false;
			state.utterance = null;
			setPlayIcon( false );
			setIndicator( false );
		}

		function speak() {
			// Cancel any ongoing speech first.
			synth.cancel();

			const step = getStep();
			if ( ! step ) return;

			const text = buildSpeechText( step, getMysteryData );
			if ( ! text.trim() ) return;

			const utter       = new SpeechSynthesisUtterance( text );
			utter.rate        = state.rate;
			utter.lang        = 'en-US';

			// On start.
			utter.onstart = function () {
				state.playing   = true;
				state.utterance = utter;
				setPlayIcon( true );
				setIndicator( true );
			};

			// On end — auto-advance if enabled and not on last step.
			utter.onend = function () {
				state.playing   = false;
				state.utterance = null;
				setPlayIcon( false );
				setIndicator( false );

				if ( state.autoAdvance ) {
					const currentIdx = getIndex();
					const total      = getTotal();
					if ( currentIdx < total - 1 ) {
						// Small delay before advancing so it feels natural.
						setTimeout( function () {
							nextStep();
							// Auto-play the next step.
							speak();
						}, 800 );
					}
				}
			};

			// On error.
			utter.onerror = function ( e ) {
				// 'interrupted' is expected when we cancel manually — ignore it.
				if ( e.error === 'interrupted' ) return;
				state.playing   = false;
				state.utterance = null;
				setPlayIcon( false );
				setIndicator( false );
			};

			synth.speak( utter );
		}

		// ── Event listeners ───────────────────────────────────────────────────

		// Play / Pause toggle.
		playBtn.addEventListener( 'click', function () {
			if ( state.playing ) {
				stopSpeech();
			} else {
				speak();
			}
		} );

		// Auto-advance toggle.
		autoChk.addEventListener( 'change', function () {
			state.autoAdvance = autoChk.checked;
		} );

		// Speed change.
		speedSel.addEventListener( 'change', function () {
			state.rate = parseFloat( speedSel.value ) || 1;
			// If currently speaking, restart with new speed.
			if ( state.playing ) {
				stopSpeech();
				speak();
			}
		} );

		// ── Public API for this instance ──────────────────────────────────────

		/**
		 * Called by the rosary app whenever the step changes.
		 * Stops current speech. If auto-advance is on and was playing, re-speaks.
		 *
		 * @param {boolean} wasAutoPlaying Whether speech was running before step change.
		 */
		function onStepChange( wasAutoPlaying ) {
			const wasPlaying = state.playing;
			stopSpeech();
			if ( wasAutoPlaying || wasPlaying ) {
				setTimeout( speak, 300 );
			}
		}

		/**
		 * Stop all speech immediately (called when mystery tab changes).
		 */
		function onMysteryChange() {
			stopSpeech();
		}

		return {
			onStepChange,
			onMysteryChange,
			stop: stopSpeech,
		};
	}

	// ── Register with the HolyRosary namespace ───────────────────────────────
	if ( typeof window.HolyRosary === 'undefined' ) {
		window.HolyRosary = {};
	}

	window.HolyRosary.Audio = {
		init: initAudio,
	};

} )();
