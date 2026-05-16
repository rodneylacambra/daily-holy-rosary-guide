/**
 * Holy Rosary - Frontend JavaScript
 *
 * Supports multiple instances on one page via the HolyRosary.init( id ) pattern.
 * All data (prayers, mysteries) is self-contained here.
 * WordPress settings & i18n strings come from holyRosaryData (wp_localize_script).
 *
 * @package HolyRosary
 */

/* global holyRosaryData */
( function () {
	'use strict';

	// ─── Config ───────────────────────────────────────────────────────────────
	const cfg      = ( typeof holyRosaryData !== 'undefined' ) ? holyRosaryData : {};
	const settings = cfg.settings  || {};
	const i18n     = cfg.i18n      || {};
	const AJAX_URL = cfg.ajaxUrl   || '';
	const NONCE    = cfg.nonce     || '';

	// ─── Day → Mystery mapping ────────────────────────────────────────────────
	const DAY_MYSTERY  = { 0: 3, 1: 0, 2: 2, 3: 3, 4: 1, 5: 2, 6: 0 };
	const ORDINALS     = [ 'First', 'Second', 'Third', 'Fourth', 'Fifth' ];
	const BEAD_LABELS  = [ '3rd', '4th', '5th', '6th' ];

	// ─── Mystery data ─────────────────────────────────────────────────────────
	const MYSTERIES = [
		{
			name: 'Joyful', full: 'Joyful Mysteries', list: [
				{ title: 'The Annunciation',          desc: 'The angel Gabriel announces to Mary that she will bear the Son of God.' },
				{ title: 'The Visitation',             desc: 'Mary visits her cousin Elizabeth, who is pregnant with John the Baptist.' },
				{ title: 'The Nativity',               desc: 'Jesus is born in Bethlehem in a humble manger.' },
				{ title: 'The Presentation',           desc: 'Mary and Joseph present the infant Jesus in the Temple.' },
				{ title: 'The Finding in the Temple',  desc: 'The young Jesus is found teaching in the Temple.' },
			],
		},
		{
			name: 'Luminous', full: 'Luminous Mysteries', list: [
				{ title: 'The Baptism of Jesus',          desc: 'Jesus is baptized in the Jordan River by John.' },
				{ title: 'The Wedding at Cana',           desc: 'Jesus performs His first miracle, turning water into wine.' },
				{ title: 'Proclamation of the Kingdom',   desc: 'Jesus calls all to conversion and announces the Kingdom.' },
				{ title: 'The Transfiguration',           desc: 'Jesus is transfigured before Peter, James, and John.' },
				{ title: 'Institution of the Eucharist',  desc: 'Jesus gives us His Body and Blood at the Last Supper.' },
			],
		},
		{
			name: 'Sorrowful', full: 'Sorrowful Mysteries', list: [
				{ title: 'The Agony in the Garden',     desc: 'Jesus prays and sweats blood in Gethsemane.' },
				{ title: 'The Scourging at the Pillar', desc: 'Jesus is brutally whipped by Roman soldiers.' },
				{ title: 'The Crowning with Thorns',    desc: 'Soldiers mock Jesus with a crown of thorns.' },
				{ title: 'The Carrying of the Cross',   desc: 'Jesus carries His cross to Calvary.' },
				{ title: 'The Crucifixion',             desc: 'Jesus is crucified and dies for our sins.' },
			],
		},
		{
			name: 'Glorious', full: 'Glorious Mysteries', list: [
				{ title: 'The Resurrection',                desc: 'Jesus rises gloriously from the dead on the third day.' },
				{ title: 'The Ascension',                   desc: 'Jesus ascends body and soul into Heaven.' },
				{ title: 'The Descent of the Holy Spirit',  desc: 'The Holy Spirit comes upon the Apostles at Pentecost.' },
				{ title: 'The Assumption of Mary',          desc: 'Mary is assumed body and soul into Heaven.' },
				{ title: 'The Coronation of Mary',          desc: 'Mary is crowned Queen of Heaven and Earth.' },
			],
		},
	];

	// ─── Prayer texts ─────────────────────────────────────────────────────────
	const PR = {
		cross:      'In the name of the Father, and of the Son, and of the Holy Spirit. Amen.',
		apostles:   'I believe in God, the Father Almighty, Creator of Heaven and earth; and in Jesus Christ, His only Son, Our Lord, who was conceived by the Holy Spirit, born of the Virgin Mary, suffered under Pontius Pilate, was crucified, died and was buried. He descended into Hell; the third day He arose again from the dead; He ascended into Heaven, and sitteth at the right hand of God, the Father Almighty; from thence He shall come to judge the living and the dead. I believe in the Holy Spirit, the holy Catholic Church, the communion of saints, the forgiveness of sins, the resurrection of the body and life everlasting. Amen.',
		ourFather:  'Our Father, who art in heaven, hallowed be Thy name; Thy kingdom come; Thy will be done on earth as it is in heaven. Give us this day our daily bread; and forgive us our trespasses as we forgive those who trespass against us; and lead us not into temptation, but deliver us from evil. Amen.',
		hailMary:   'Hail Mary, full of grace, the Lord is with thee. Blessed art thou among women, and blessed is the fruit of thy womb, Jesus. Holy Mary, Mother of God, pray for us sinners, now and at the hour of our death. Amen.',
		gloryBe:    'Glory be to the Father, and to the Son, and to the Holy Spirit. As it was in the beginning, is now, and ever shall be, world without end. Amen.',
		fatima:     'O my Jesus, forgive us our sins, save us from the fires of hell. Lead all souls to Heaven, especially those in most need of Thy mercy. Amen.',
		hailHolyQueen: 'Hail Holy Queen, Mother of Mercy, our Life, our Sweetness, and our hope. To thee we cry, poor banished children of Eve. To thee we send up our sighs, mourning and weeping in this vale of tears. Turn then most gracious advocate, Thine eyes of mercy toward us, and after this, our exile, show unto us, the blessed fruit of thy womb, Jesus. O clement, O loving, O sweet Virgin Mary. Pray for us O Holy Mother of God, That we may be made worthy of the promises of Christ. Amen.',
		finalPrayer: 'Let us pray. O God, whose only begotten Son, by His life, death, and resurrection, has purchased for us the rewards of eternal life, grant, we beseech Thee, that meditating upon these mysteries of the Most Holy Rosary of the Blessed Virgin Mary, we may imitate what they contain and obtain what they promise, through the same Christ Our Lord. Amen.',
		litany: 'Lord, have mercy. Christ, have mercy. Lord, have mercy.\nChrist, hear us. Christ, graciously hear us.\nGod the Father of Heaven, have mercy on us.\nGod the Son, Redeemer of the world, have mercy on us.\nGod the Holy Spirit, have mercy on us.\nHoly Trinity, One God, have mercy on us.\nHoly Mary, pray for us.\nHoly Mother of God, pray for us.\nHoly Virgin of Virgins, pray for us.\nMother of Christ, pray for us.\nMother of the Church, pray for us.\nMother of Divine Grace, pray for us.\nMother most pure, pray for us.\nMother most chaste, pray for us.\nMother inviolate, pray for us.\nMother undefiled, pray for us.\nMother most amiable, pray for us.\nMother most admirable, pray for us.\nMother of good counsel, pray for us.\nMother of our Creator, pray for us.\nMother of our Saviour, pray for us.\nVirgin most prudent, pray for us.\nVirgin most venerable, pray for us.\nVirgin most renowned, pray for us.\nVirgin most powerful, pray for us.\nVirgin most merciful, pray for us.\nVirgin most faithful, pray for us.\nMirror of justice, pray for us.\nSeat of wisdom, pray for us.\nCause of our joy, pray for us.\nSpiritual vessel, pray for us.\nVessel of honour, pray for us.\nSingular vessel of devotion, pray for us.\nMystical rose, pray for us.\nTower of David, pray for us.\nTower of ivory, pray for us.\nHouse of gold, pray for us.\nArk of the covenant, pray for us.\nGate of Heaven, pray for us.\nMorning star, pray for us.\nHealth of the sick, pray for us.\nRefuge of sinners, pray for us.\nComforter of the afflicted, pray for us.\nHelp of Christians, pray for us.\nQueen of Angels, pray for us.\nQueen of Patriarchs, pray for us.\nQueen of Prophets, pray for us.\nQueen of Apostles, pray for us.\nQueen of Martyrs, pray for us.\nQueen of Confessors, pray for us.\nQueen of Virgins, pray for us.\nQueen of all Saints, pray for us.\nQueen conceived without original sin, pray for us.\nQueen assumed into Heaven, pray for us.\nQueen of the most holy Rosary, pray for us.\nQueen of families, pray for us.\nQueen of peace, pray for us.\nLamb of God, who takest away the sins of the world, spare us, O Lord.\nLamb of God, who takest away the sins of the world, graciously hear us, O Lord.\nLamb of God, who takest away the sins of the world, have mercy on us.\nPray for us, O holy Mother of God, that we may be made worthy of the promises of Christ.\nLet us pray. Grant, we beseech Thee, O Lord God, unto us Thy servants, that we may rejoice in continual health of mind and body; and, by the glorious intercession of Blessed Mary ever Virgin, may be delivered from present sadness and enter into the joy of Thine eternal gladness. Through Christ Our Lord. Amen.',
		stMichael: 'Saint Michael the Archangel, defend us in battle. Be our protection against the wickedness and snares of the devil. May God rebuke him, we humbly pray; and do Thou, O Prince of the Heavenly Host, by the Divine Power of God, cast into hell Satan and all the evil spirits who roam throughout the world seeking the ruin of souls. Amen.',
		stJoseph:  'O Blessed Saint Joseph, faithful guardian and protector of virgins, to whom God entrusted Jesus and Mary, I implore thee by the love thou didst bear them, to preserve me from every defilement of soul and body, that I may always serve them in holiness and purity of love. Amen.',
	};

	// ─── Geometry constants ───────────────────────────────────────────────────
	const Rs = 4, Rl = 7, GAP = 4, HR = 10;

	// ─── Build loop bead sequence (54 beads: 4 large + 50 small) ─────────────
	const LOOP_SEQ  = [];
	const LOOP_KEYS = [];
	( function () {
		const aL = ( k ) => { LOOP_SEQ.push( Rl ); LOOP_KEYS.push( { key: k, large: true  } ); };
		const aS = ( k ) => { LOOP_SEQ.push( Rs ); LOOP_KEYS.push( { key: k, large: false } ); };
		for ( let i = 0; i < 10; i++ ) aS( `d1s${ i }` );
		aL( 'large_3' ); for ( let i = 0; i < 10; i++ ) aS( `d2s${ i }` );
		aL( 'large_4' ); for ( let i = 0; i < 10; i++ ) aS( `d3s${ i }` );
		aL( 'large_5' ); for ( let i = 0; i < 10; i++ ) aS( `d4s${ i }` );
		aL( 'large_6' ); for ( let i = 0; i < 10; i++ ) aS( `d5s${ i }` );
	} )();

	const N           = LOOP_SEQ.length;
	const sumR        = LOOP_SEQ.reduce( ( a, b ) => a + b, 0 );
	const heartSlot   = HR * 2 + GAP * 2;
	const CIRC        = 2 * sumR + N * GAP + heartSlot;
	const R           = CIRC / ( 2 * Math.PI );

	// ─── Build per-instance positions ─────────────────────────────────────────
	function buildPositions( CX, JY, CY ) {
		const bp = {};
		let arc = heartSlot / 2 + LOOP_SEQ[ 0 ];

		for ( let i = 0; i < N; i++ ) {
			if ( i > 0 ) arc += LOOP_SEQ[ i - 1 ] + GAP + LOOP_SEQ[ i ];
			const angle = ( Math.PI / 2 ) - ( arc / R );
			bp[ LOOP_KEYS[ i ].key ] = {
				x: CX + R * Math.cos( angle ),
				y: CY + R * Math.sin( angle ),
				large: LOOP_KEYS[ i ].large,
			};
		}

		bp.heart = { x: CX, y: JY, heart: true };
		let y = JY;
		y += HR + GAP + Rl; bp.large_2  = { x: CX, y, large: true  };
		y += Rl + GAP + Rs; bp.ts2      = { x: CX, y, large: false };
		y += Rs + GAP + Rs; bp.ts1      = { x: CX, y, large: false };
		y += Rs + GAP + Rs; bp.ts0      = { x: CX, y, large: false };
		y += Rs + GAP + Rl; bp.large_1  = { x: CX, y, large: true  };
		y += Rl + GAP + 10; bp.cross    = { x: CX, y, cross: true  };
		return bp;
	}

	// ─── Build steps ──────────────────────────────────────────────────────────
	function buildSteps( mysteryIdx ) {
		const m = MYSTERIES[ mysteryIdx ];
		const S = [];

		S.push( { num: 'Welcome', title: `Welcome! Let us begin and meditate on the ${ m.full } of the Holy Rosary.`, desc: 'Hold your rosary, find a quiet place, and let us pray together.', prayers: [], bk: 'none' } );
		S.push( { num: 'Cross',        title: 'Sign of the Cross',             desc: 'Hold the Crucifix. Make the Sign of the Cross to begin.',                       prayers: [ { l: 'Sign of the Cross', t: PR.cross } ], bk: 'cross' } );
		S.push( { num: '1st Large Bead', title: 'Apostles\' Creed & Our Father', desc: 'On the first large bead right above the cross, pray the Apostles\' Creed then the Our Father.', prayers: [ { l: 'Apostles\' Creed', t: PR.apostles }, { l: 'Our Father', t: PR.ourFather } ], bk: 'large_1' } );
		S.push( { num: '1st Small Bead', title: 'Hail Mary — for Faith',   desc: 'Pray the Hail Mary for an increase in Faith.',   prayers: [ { l: 'Hail Mary', t: PR.hailMary } ], bk: 'ts0' } );
		S.push( { num: '2nd Small Bead', title: 'Hail Mary — for Hope',    desc: 'Pray the Hail Mary for an increase in Hope.',    prayers: [ { l: 'Hail Mary', t: PR.hailMary } ], bk: 'ts1' } );
		S.push( { num: '3rd Small Bead', title: 'Hail Mary — for Charity', desc: 'Pray the Hail Mary for an increase in Charity.', prayers: [ { l: 'Hail Mary', t: PR.hailMary } ], bk: 'ts2' } );
		S.push( { num: '2nd Large Bead', title: `Glory Be, Announce Mystery 1 & Our Father`, desc: `Pray the Glory Be, then announce the ${ ORDINALS[ 0 ] } ${ m.name } Mystery, meditate briefly, then pray the Our Father. From here the loop begins — move to the RIGHT.`, prayers: [ { l: 'Glory Be', t: PR.gloryBe }, { l: 'Our Father', t: PR.ourFather } ], mystery: 0, bk: 'large_2' } );

		const dSmalls = [ 0, 1, 2, 3, 4 ].map( d => Array.from( { length: 10 }, ( _, i ) => `d${ d + 1 }s${ i }` ) );
		const lKeys   = [ 'large_3', 'large_4', 'large_5', 'large_6' ];

		for ( let d = 0; d < 5; d++ ) {
			for ( let hm = 0; hm < 10; hm++ ) {
				S.push( { num: `Decade ${ d + 1 } — Bead ${ hm + 1 } of 10`, title: `Hail Mary ${ hm + 1 } of 10`, desc: `Pray the Hail Mary on bead ${ hm + 1 }, meditating on the ${ ORDINALS[ d ] } ${ m.name } Mystery.`, prayers: [ { l: 'Hail Mary', t: PR.hailMary } ], bk: dSmalls[ d ][ hm ], decadeDots: { filled: hm, total: 10 } } );
			}
			S.push( { num: `Decade ${ d + 1 } — Closing`, title: 'Glory Be & Fatima Prayer', desc: 'After the 10th Hail Mary, pray the Glory Be then the Fatima Prayer.', prayers: [ { l: 'Glory Be', t: PR.gloryBe }, { l: 'Fatima Prayer', t: PR.fatima } ], bk: dSmalls[ d ][ 9 ] } );
			if ( d < 4 ) {
				S.push( { num: `${ BEAD_LABELS[ d ] } Large Bead`, title: `Announce Mystery ${ d + 2 } & Our Father`, desc: `Announce the ${ ORDINALS[ d + 1 ] } ${ m.name } Mystery, meditate briefly, then pray the Our Father.`, prayers: [ { l: 'Our Father', t: PR.ourFather } ], mystery: d + 1, bk: lKeys[ d ] } );
			}
		}

		S.push( { num: '❤️ Heart — Closing 1',    title: 'Hail Holy Queen',                    desc: 'The loop is complete. You are at the Heart. Pray the Hail Holy Queen.',   prayers: [ { l: 'Hail Holy Queen', t: PR.hailHolyQueen } ], bk: 'heart', heart: true } );
		S.push( { num: '❤️ Heart — Closing 2',    title: 'Final Prayer',                        desc: 'Continue with the Final Prayer.',                                          prayers: [ { l: 'Final Prayer', t: PR.finalPrayer } ], bk: 'heart', heart: true } );
		S.push( { num: '❤️ Heart — Additional 1', title: 'Litany of the Blessed Virgin Mary',   desc: 'Pray the Litany of the Blessed Virgin Mary.',                              prayers: [ { l: 'Litany of the Blessed Virgin Mary', t: PR.litany } ], bk: 'heart', heart: true, additional: true } );
		S.push( { num: '❤️ Heart — Additional 2', title: 'Prayer to Saint Michael',             desc: 'Pray the Prayer to Saint Michael the Archangel.',                          prayers: [ { l: 'Prayer to Saint Michael the Archangel', t: PR.stMichael } ], bk: 'heart', heart: true, additional: true } );
		S.push( { num: '❤️ Heart — Additional 3', title: 'Prayer to Saint Joseph',              desc: 'Pray the Prayer to Saint Joseph.',                                         prayers: [ { l: 'Prayer to Saint Joseph', t: PR.stJoseph } ], bk: 'heart', heart: true, additional: true } );
		S.push( { num: 'Sign of the Cross', title: 'Sign of the Cross', desc: 'Conclude by making the Sign of the Cross.', prayers: [ { l: 'Sign of the Cross', t: PR.cross } ], bk: 'all_done' } );
		S.push( { num: 'Finished! 🎉', title: 'You have prayed the Rosary!', desc: `Praise God! You have completed the ${ m.full }. May Our Lady intercede for you and your intentions.`, prayers: [], bk: 'all_done', finished: true } );

		return S;
	}

	// ─── Heart drawing ────────────────────────────────────────────────────────
	function drawHeart( ctx, hx, hy, r, fill, stroke, glow ) {
		if ( glow ) {
			ctx.fillStyle = 'rgba(232,33,58,0.25)';
			ctx.beginPath(); ctx.arc( hx, hy, r + 6, 0, 2 * Math.PI ); ctx.fill();
		}
		ctx.fillStyle = fill; ctx.strokeStyle = stroke; ctx.lineWidth = 1.2;
		ctx.beginPath();
		ctx.moveTo( hx, hy + r * 0.35 );
		ctx.bezierCurveTo( hx, hy, hx - r * 1.1, hy - r * 0.6, hx - r * 1.1, hy - r * 0.15 );
		ctx.bezierCurveTo( hx - r * 1.1, hy + r * 0.5, hx, hy + r * 1.0, hx, hy + r * 1.0 );
		ctx.bezierCurveTo( hx, hy + r * 1.0, hx + r * 1.1, hy + r * 0.5, hx + r * 1.1, hy - r * 0.15 );
		ctx.bezierCurveTo( hx + r * 1.1, hy - r * 0.6, hx, hy, hx, hy + r * 0.35 );
		ctx.closePath(); ctx.fill(); ctx.stroke();
		ctx.fillStyle = 'rgba(255,255,255,0.28)';
		ctx.beginPath(); ctx.ellipse( hx - r * 0.3, hy - r * 0.1, r * 0.22, r * 0.16, -0.3, 0, 2 * Math.PI ); ctx.fill();
	}

	// ─── Instance factory ─────────────────────────────────────────────────────
	function createInstance( rootId ) {
		const root    = document.getElementById( rootId );
		if ( ! root ) return;

		const canvas  = document.getElementById( `${ rootId }-canvas` );
		if ( ! canvas ) return;

		// State.
		const state = {
			mysteryIdx:   null,
			todayMystery: DAY_MYSTERY[ new Date().getDay() ],
			step:         0,
			steps:        [],
			startTime:    null,
			BP:           null,
			CW:           0,
			CX:           0,
			CY:           0,
			JY:           0,
			CH:           0,
		};

		// Determine which mystery to default to.
		// Priority:
		//  1. If shortcode/block sets an explicit mystery (e.g. mystery="joyful") — use it.
		//  2. Always default to today's day-based mystery (auto-detect).
		//     The autoDetectMystery admin setting only controls the UI banner indicator,
		//     NOT the default mystery — we always load the correct mystery for today.
		const attrMystery = root.getAttribute( 'data-mystery' ) || 'auto';
		const mysteryMap  = { joyful: 0, luminous: 1, sorrowful: 2, glorious: 3 };

		if ( attrMystery !== 'auto' && Object.prototype.hasOwnProperty.call( mysteryMap, attrMystery ) ) {
			// Shortcode explicitly forced a specific mystery.
			state.mysteryIdx = mysteryMap[ attrMystery ];
		} else {
			// Default: always load today's mystery based on day of the week.
			state.mysteryIdx = state.todayMystery;
		}

		// ── Geometry ──────────────────────────────────────────────────────────
		function recalcGeometry() {
			state.CW = Math.min( root.offsetWidth || 310, 310 );
			state.CX = state.CW / 2;
			state.CY = R + 14;
			state.JY = state.CY + R;
			state.BP = buildPositions( state.CX, state.JY, state.CY );
			const crossY = state.BP.cross.y;
			state.CH = Math.ceil( crossY + 24 / 2 + 16 );
		}

		// ── Draw rosary ────────────────────────────────────────────────────────
		function drawRosary() {
			const { CX, CY, JY, CW, CH, BP, steps, step } = state;
			const ctx = canvas.getContext( '2d' );
			const dpr = window.devicePixelRatio || 1;

			canvas.width  = CW * dpr;
			canvas.height = CH * dpr;
			canvas.style.width  = CW + 'px';
			canvas.style.height = CH + 'px';
			ctx.scale( dpr, dpr );
			ctx.clearRect( 0, 0, CW, CH );

			const activeKey   = steps[ step ] ? steps[ step ].bk : 'none';
			const allDone     = activeKey === 'all_done';
			const heartActive = activeKey === 'heart';

			// Build done set.
			const doneKeys = new Set();
			if ( allDone ) {
				Object.keys( BP ).forEach( k => doneKeys.add( k ) );
			} else {
				let found = false;
				for ( let i = 0; i < steps.length; i++ ) {
					if ( ! found && steps[ i ].bk === activeKey && ! steps[ i ].heart ) { found = true; continue; }
					if ( ! found ) doneKeys.add( steps[ i ].bk );
				}
			}

			function st( k ) {
				if ( k === 'heart' ) {
					if ( heartActive ) return 'cur';
					if ( allDone ) return 'done';
					return 'empty';
				}
				if ( allDone ) return 'done';
				return k === activeKey ? 'cur' : doneKeys.has( k ) ? 'done' : 'empty';
			}

			const GOLD = '#D4A843', GOLDS = '#9A7010', GDARK = '#7B5500', GDARKS = '#3d2900';
			const BLUE = '#1A3FCC', BLUES = '#0E2899', BDARK = '#0A1A88', BDARKS = '#060F55';
			const CHAIN = '#C8A84A', GG = 'rgba(212,168,67,0.3)', GB = 'rgba(26,63,204,0.3)';
			const HRED = '#E8213A', HREDS = '#8B0F1E', HDARK = '#7a0e1e', HDARKS = '#4a0008';

			const LP0 = BP[ LOOP_KEYS[ 0 ].key ];
			const LPN = BP[ LOOP_KEYS[ N - 1 ].key ];

			// Circle chain.
			ctx.strokeStyle = CHAIN; ctx.lineWidth = 1.5;
			ctx.beginPath(); ctx.arc( CX, CY, R, 0, 2 * Math.PI ); ctx.stroke();

			// Tail chain.
			const crossY = BP.cross.y;
			ctx.beginPath(); ctx.moveTo( CX, JY ); ctx.lineTo( CX, crossY + 12 ); ctx.stroke();

			// 3-way stubs from heart.
			[ LP0, LPN, BP.large_2 ].forEach( nb => {
				ctx.beginPath(); ctx.moveTo( CX, JY ); ctx.lineTo( nb.x, nb.y ); ctx.stroke();
			} );

			// Ring above cross.
			ctx.strokeStyle = CHAIN; ctx.lineWidth = 1.2;
			ctx.beginPath(); ctx.arc( CX, crossY - 7, 3, 0, 2 * Math.PI ); ctx.stroke();

			// Cross.
			const cp = BP.cross, cs = st( 'cross' );
			const cFill = cs === 'done' ? GDARK : GOLD, cStr = cs === 'done' ? GDARKS : GOLDS;
			if ( cs === 'cur' ) { ctx.fillStyle = GG; ctx.beginPath(); ctx.arc( cp.x, cp.y + 2, 14, 0, 2 * Math.PI ); ctx.fill(); }
			ctx.fillStyle = cFill; ctx.strokeStyle = cStr; ctx.lineWidth = 1;
			ctx.beginPath(); ctx.roundRect( cp.x - 4, cp.y - 9, 8, 24, 2 ); ctx.fill(); ctx.stroke();
			ctx.beginPath(); ctx.roundRect( cp.x - 11, cp.y - 1, 22, 7, 2 ); ctx.fill(); ctx.stroke();

			// Beads — two passes (normal then active on top).
			for ( const pass of [ 0, 1 ] ) {
				for ( const [ key, p ] of Object.entries( BP ) ) {
					if ( p.cross || p.heart ) continue;
					const s = st( key ), cur = s === 'cur';
					if ( pass === 0 && cur ) continue;
					if ( pass === 1 && ! cur ) continue;
					const isL = p.large, r = isL ? Rl : Rs;
					const fill = isL ? ( s === 'done' ? BDARK : BLUE ) : ( s === 'done' ? GDARK : GOLD );
					const str  = isL ? ( s === 'done' ? BDARKS : BLUES ) : ( s === 'done' ? GDARKS : GOLDS );
					if ( cur ) { ctx.fillStyle = isL ? GB : GG; ctx.beginPath(); ctx.arc( p.x, p.y, r + 4, 0, 2 * Math.PI ); ctx.fill(); }
					ctx.fillStyle = fill; ctx.strokeStyle = str; ctx.lineWidth = isL ? 1.1 : 0.7;
					ctx.beginPath(); ctx.arc( p.x, p.y, r, 0, 2 * Math.PI ); ctx.fill(); ctx.stroke();
					ctx.fillStyle = 'rgba(255,255,255,0.22)';
					ctx.beginPath(); ctx.arc( p.x - r * 0.28, p.y - r * 0.3, r * 0.3, 0, 2 * Math.PI ); ctx.fill();
				}
			}

			// Heart — always on top.
			const hs = st( 'heart' );
			drawHeart( ctx, CX, JY, HR, hs === 'done' ? HDARK : HRED, hs === 'done' ? HDARKS : HREDS, hs === 'cur' );
		}

		// ── Render UI ──────────────────────────────────────────────────────────
		function render() {
			recalcGeometry();
			state.steps = buildSteps( state.mysteryIdx );

			const s     = state.steps[ state.step ];
			const total = state.steps.length - 1;
			const m     = MYSTERIES[ state.mysteryIdx ];
			const today = new Date();
			const dow   = today.getDay();

			const DAY_NAMES   = i18n.days   || [ 'Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday' ];
			const MONTH_NAMES = i18n.months || [ 'January','February','March','April','May','June','July','August','September','October','November','December' ];

			// ── SETTING: Show Date Banner ─────────────────────────────────────
			const bannerEl = document.getElementById( `${ rootId }-date-banner` );
			if ( bannerEl ) {
				bannerEl.style.display = settings.showDateBanner ? '' : 'none';
			}
			if ( settings.showDateBanner ) {
				const dateEl = document.getElementById( `${ rootId }-date` );
				if ( dateEl ) {
					dateEl.textContent = `📅 Today is ${ DAY_NAMES[ dow ] }, ${ today.getDate() } ${ MONTH_NAMES[ today.getMonth() ] } ${ today.getFullYear() }`;
				}
				const todayLabel = document.getElementById( `${ rootId }-mystery-today` );
				if ( todayLabel ) {
					todayLabel.innerHTML = `Today's Mystery: <span class="hr-mystery-pill">${ MYSTERIES[ state.todayMystery ].full }</span>`;
				}
			}

			// ── SETTING: Auto-Detect Mystery (controls today-dot indicator) ───
			// NOTE: The actual mystery default is always today — this setting only
			// controls whether the red dot / today highlight shows on the tabs.
			for ( let i = 0; i < 4; i++ ) {
				const tab = document.getElementById( `${ rootId }-tab-${ i }` );
				if ( ! tab ) continue;
				const dot = tab.querySelector( '.hr-tab-dot' );
				if ( dot ) dot.remove();
				tab.classList.toggle( 'is-active', i === state.mysteryIdx );
				// Only show today indicator if setting is on.
				const showToday = settings.autoDetectMystery && ( i === state.todayMystery );
				tab.classList.toggle( 'is-today', showToday );
				if ( showToday ) {
					const d = document.createElement( 'span' );
					d.className = 'hr-tab-dot'; tab.appendChild( d );
				}
			}

			// Step content.
			const setHTML = ( id, html ) => { const el = document.getElementById( id ); if ( el ) el.innerHTML = html; };
			const setText = ( id, txt )  => { const el = document.getElementById( id ); if ( el ) el.textContent = txt; };

			setText( `${ rootId }-step-num`,   s.num );
			setText( `${ rootId }-step-title`, s.title );
			setText( `${ rootId }-step-desc`,  s.desc );

			// Tag.
			const tagHTML = s.additional ? '<span class="hr-additional-tag">✨ Additional Prayer</span>'
			              : s.heart      ? '<span class="hr-heart-tag">❤️ Heart — Closing Prayers</span>'
			              : '';
			setHTML( `${ rootId }-tag-box`, tagHTML );

			// Prayers.
			const prayerHTML = ( s.prayers || [] ).map( p =>
				`<div class="hr-prayer-block"><div class="hr-prayer-label">${ p.l }</div><div class="hr-prayer-text">${ p.t }</div></div>`
			).join( '' );
			setHTML( `${ rootId }-prayer-box`, prayerHTML );

			// Mystery card.
			const mysteryHTML = s.mystery !== undefined
				? `<div class="hr-mystery-card">
					<div class="hr-mystery-ordinal">The ${ ORDINALS[ s.mystery ] } ${ m.name } Mystery</div>
					<div class="hr-mystery-name">${ m.list[ s.mystery ].title }</div>
					<div class="hr-mystery-desc">${ m.list[ s.mystery ].desc }</div>
				   </div>`
				: '';
			setHTML( `${ rootId }-mystery-box`, mysteryHTML );

			// ── SETTING: Show Decade Dots ─────────────────────────────────────
			const dotsHTML = ( s.decadeDots && settings.showDecadeDots )
				? `<div class="hr-decade-dots">${ Array.from( { length: s.decadeDots.total }, ( _, i ) => `<span class="hr-dot ${ i < s.decadeDots.filled ? 'is-filled' : i === s.decadeDots.filled ? 'is-active' : '' }"></span>` ).join( '' ) }</div>`
				: '';
			setHTML( `${ rootId }-decade-dots`, dotsHTML );

			// Counter & progress.
			setText( `${ rootId }-counter`, `Step ${ state.step } of ${ total }` );
			const progressEl = document.getElementById( `${ rootId }-progress` );
			if ( progressEl ) {
				progressEl.textContent = state.step === 0
					? 'Your progress — tap Next to begin'
					: state.step === total
						? '🎉 Rosary complete! God bless you.'
						: `Progress: ${ Math.round( ( state.step / total ) * 100 ) }% complete`;
			}

			// Draw canvas.
			drawRosary();

			// Save session when finished.
			if ( s.finished && state.startTime ) {
				const duration = Math.round( ( Date.now() - state.startTime ) / 1000 );
				saveSession( m.name.toLowerCase(), duration );
				state.startTime = null;
			}
		}

		// ── Audio module reference ─────────────────────────────────────────────
		let audio = null;

		// ── Navigation ─────────────────────────────────────────────────────────
		function nextStep() {
			if ( state.step === 0 ) state.startTime = Date.now();
			if ( state.step < state.steps.length - 1 ) {
				state.step++;
				render();
				// Notify audio module — it will re-speak if auto-advance is on.
				if ( audio ) audio.onStepChange( false );
			}
		}

		function prevStep() {
			if ( state.step > 0 ) {
				state.step--;
				render();
				// Stop speech when going back manually.
				if ( audio ) audio.stop();
			}
		}

		function setMystery( idx ) {
			state.mysteryIdx = idx;
			state.step = 0;
			// Stop speech when switching mystery.
			if ( audio ) audio.onMysteryChange();
			render();
		}

		// ── AJAX: save session ─────────────────────────────────────────────────
		function saveSession( mysterySet, durationSecs ) {
			if ( ! cfg.isLoggedIn || ! AJAX_URL ) return;

			const body = new URLSearchParams( {
				action:       'holy_rosary_save_session',
				nonce:         NONCE,
				mystery_set:   mysterySet,
				duration_secs: durationSecs,
			} );

			fetch( AJAX_URL, { method: 'POST', body, credentials: 'same-origin' } )
				.then( r => r.json() )
				.catch( () => {} );
		}

		// ── Bind events ────────────────────────────────────────────────────────
		const btnNext = document.getElementById( `${ rootId }-btn-next` );
		const btnBack = document.getElementById( `${ rootId }-btn-back` );
		if ( btnNext ) btnNext.addEventListener( 'click', nextStep );
		if ( btnBack ) btnBack.addEventListener( 'click', prevStep );

		for ( let i = 0; i < 4; i++ ) {
			const tab = document.getElementById( `${ rootId }-tab-${ i }` );
			if ( tab ) {
				tab.addEventListener( 'click', ( () => {
					const idx = i;
					return () => setMystery( idx );
				} )() );
			}
		}

		// ── Initialise Audio module if available and enabled ───────────────────
		const audioEnabled = settings.enableAudio !== false; // default true
		if ( audioEnabled && window.HolyRosary && window.HolyRosary.Audio ) {
			audio = window.HolyRosary.Audio.init(
				rootId,
				() => state.steps[ state.step ],       // getStep
				nextStep,                               // nextStep
				() => state.steps.length,              // getTotal
				() => state.step,                      // getIndex
				() => MYSTERIES[ state.mysteryIdx ]    // getMysteryData
			);
		}

		// Initial render.
		recalcGeometry();
		render();
	}

	// ─── Public API ───────────────────────────────────────────────────────────
	window.HolyRosary = {
		init: createInstance,
	};

} )();
