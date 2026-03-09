import React, { useState, useEffect, useCallback, useRef, Suspense } from 'react';
import HighlightOverlay from './HighlightOverlay';
import TourSpeechBubble from './TourSpeechBubble';
import TourAssistantCharacter from './TourAssistantCharacter2D';
import AssistantChatWidget from './AssistantChatWidget';

/* ─── Storage key ─── */
const TOUR_DONE_PREFIX = 'hormonelens_tour_completed';

/* ─── Tour step definitions ─── */
const TOUR_STEPS = [
    {
        id: 'welcome',
        selector: null,
        text: "Hey! I'm HormoneLens, your health buddy. Let me show you around your dashboard!",
        characterAnim: 'wave',
    },
    {
        id: 'risk-prediction',
        selector: '[data-tour-id="score-cards"]',
        text: 'These cards show your hormone risk scores. You can see how different areas of your health are doing.',
        characterAnim: 'idle',
    },
    {
        id: 'health-metrics',
        selector: '[data-tour-id="twin-header"]',
        text: 'Here are your key health numbers at a glance — like your BMI and overall risk level.',
        characterAnim: 'idle',
    },
    {
        id: 'historical-trends',
        selector: '[data-tour-id="body-map"]',
        text: 'This is your body map! Hover over any zone to see detailed hormone insights.',
        characterAnim: 'clap',
    },
    {
        id: 'simulation-history',
        selector: '[data-tour-id="nav-simulations"]',
        text: 'Track your past simulations here. See how your health has improved over time!',
        characterAnim: 'idle',
    },
    {
        id: 'ai-recommendations',
        selector: '[data-tour-id="nav-knowledge"]',
        text: 'Get personalized lifestyle tips from our AI based on your hormonal profile.',
        characterAnim: 'idle',
    },
    {
        id: 'profile-settings',
        selector: '[data-tour-id="nav-health-profile"]',
        text: 'Update your health profile anytime to keep your twin accurate and up-to-date.',
        characterAnim: 'idle',
    },
    {
        id: 'finish',
        selector: null,
        text: "That's your HormoneLens dashboard! I'll be right here whenever you need me. Chat with me anytime!",
        characterAnim: 'wave',
    },
];

const TOTAL_STEPS        = TOUR_STEPS.length;
const TOUR_START_DELAY   = 1200;
const EXIT_ANIM_DURATION = 800;

/* ─── Layout constants ─── */
const CHAR_W      = 120;   // character container width  (desktop)
const CHAR_H      = 200;   // character container height (desktop)
const CHAR_W_SM   = 80;    // mobile
const CHAR_H_SM   = 130;   // mobile
const BUBBLE_W    = 320;   // speech bubble width
const BUBBLE_H    = 210;   // estimated bubble height
const SAFE_DIST   = 48;    // minimum gap from highlighted element
const PAD         = 16;    // screen-edge padding
const GAP         = 12;    // gap between character and bubble

const POSITION_TRANS =
    'left 0.4s ease, top 0.4s ease, right 0.4s ease, bottom 0.4s ease, ' +
    'opacity 0.35s ease, width 0.3s ease, height 0.3s ease';

/* ─── Hook: measure a DOM element's bounding rect ─── */
function useMeasuredRect(selector, active) {
    const [rect, setRect] = useState(null);
    const rafRef          = useRef(null);

    useEffect(() => {
        if (!selector || !active) { setRect(null); return; }

        const measure = () => {
            const el = document.querySelector(selector);
            if (!el) { setRect(null); return; }
            const r = el.getBoundingClientRect();
            setRect({ x: r.x, y: r.y, width: r.width, height: r.height,
                       right: r.right, bottom: r.bottom });
        };

        // Scroll target into view on first measure, then re-measure
        const el = document.querySelector(selector);
        if (el) el.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'nearest' });

        // Allow scroll to settle before measuring
        const init = setTimeout(() => {
            measure();
        }, 160);

        const handleChange = () => {
            cancelAnimationFrame(rafRef.current);
            rafRef.current = requestAnimationFrame(measure);
        };

        window.addEventListener('resize', handleChange);
        window.addEventListener('scroll', handleChange, true);

        return () => {
            clearTimeout(init);
            window.removeEventListener('resize', handleChange);
            window.removeEventListener('scroll', handleChange, true);
            cancelAnimationFrame(rafRef.current);
        };
    }, [selector, active]);

    return rect;
}

/* ─── Which side of the viewport has the most space relative to rect ─── */
function computeBestSide(rect, vw, vh) {
    if (!rect) return 'right';

    const spaceRight  = vw - rect.right;
    const spaceLeft   = rect.x;
    const spaceBottom = vh - rect.bottom;
    const spaceTop    = rect.y;

    // On narrow screens prefer vertical placement (avoids cramped horizontal layout)
    if (vw < 640) {
        return spaceBottom >= spaceTop ? 'bottom' : 'top';
    }

    return [
        ['right',  spaceRight],
        ['left',   spaceLeft],
        ['bottom', spaceBottom],
        ['top',    spaceTop],
    ].reduce((best, cur) => (cur[1] > best[1] ? cur : best))[0];
}

/* ─── Compute pixel positions for character and speech bubble ─── */
function computeLayout(rect, exiting, vw, vh) {
    const sm = vw < 640;
    const cw = sm ? CHAR_W_SM : CHAR_W;
    const ch = sm ? CHAR_H_SM : CHAR_H;
    const bw = sm ? Math.min(BUBBLE_W, vw - PAD * 2) : BUBBLE_W;

    // ── Exiting: shrink to corner ──────────────────────────────────
    if (exiting) {
        return {
            charStyle: {
                position: 'fixed', zIndex: 9998,
                right: PAD, bottom: PAD, width: 52, height: 52,
                opacity: 0, borderRadius: '50%', overflow: 'hidden',
                transition: POSITION_TRANS,
            },
            bubbleStyle: {
                position: 'fixed', zIndex: 9999,
                right: PAD + 60, bottom: PAD,
                opacity: 0, transform: 'scale(0.75)',
                transition: 'all 0.4s ease', pointerEvents: 'none',
            },
            tailSide: 'right', bw,
        };
    }

    // ── No highlight target (welcome / finish screens) ─────────────
    if (!rect) {
        return {
            charStyle: {
                position: 'fixed', zIndex: 9998,
                right: PAD, bottom: PAD + 40, width: cw, height: ch,
                opacity: 1, transition: POSITION_TRANS,
            },
            bubbleStyle: {
                position: 'fixed', zIndex: 9999,
                right: PAD + cw + GAP,
                bottom: PAD + 40 + Math.max(0, (ch - BUBBLE_H) / 2),
                opacity: 1, transform: 'none',
                transition: 'all 0.4s ease',
            },
            tailSide: 'right', bw,
        };
    }

    // ── Dynamic positioning ────────────────────────────────────────
    const side  = computeBestSide(rect, vw, vh);
    const elCx  = rect.x + rect.width  / 2;
    const elCy  = rect.y + rect.height / 2;

    const clampX  = (x) => Math.max(PAD, Math.min(vw - bw  - PAD, x));
    const clampY  = (y) => Math.max(PAD, Math.min(vh - BUBBLE_H - PAD, y));
    const clampCX = (x) => Math.max(PAD, Math.min(vw - cw  - PAD, x));
    const clampCY = (y) => Math.max(PAD, Math.min(vh - ch  - PAD, y));

    let charStyle, bubbleStyle, tailSide;

    if (side === 'right') {
        // element │ SAFE_DIST │ bubble │ GAP │ char
        const bLeft = rect.right + SAFE_DIST;
        const cLeft = bLeft + bw + GAP;
        const topY  = elCy - BUBBLE_H / 2;

        if (cLeft + cw > vw - PAD) {
            // Not enough room horizontally → stack char below bubble
            const bL = clampX(bLeft);
            const bT = clampY(topY);
            charStyle   = { position: 'fixed', zIndex: 9998, left: clampCX(bL + bw / 2 - cw / 2), top: clampCY(bT + BUBBLE_H + GAP), width: cw, height: ch, opacity: 1, transition: POSITION_TRANS };
            bubbleStyle = { position: 'fixed', zIndex: 9999, left: bL, top: bT, opacity: 1, transform: 'none', transition: 'all 0.4s ease' };
            tailSide    = 'left';
        } else {
            charStyle   = { position: 'fixed', zIndex: 9998, left: clampCX(cLeft), top: clampCY(elCy - ch / 2), width: cw, height: ch, opacity: 1, transition: POSITION_TRANS };
            bubbleStyle = { position: 'fixed', zIndex: 9999, left: clampX(bLeft), top: clampY(topY), opacity: 1, transform: 'none', transition: 'all 0.4s ease' };
            tailSide    = 'right'; // bubble tail on right → points toward char (which is right of bubble)
        }

    } else if (side === 'left') {
        // char │ GAP │ bubble │ SAFE_DIST │ element
        const bLeft = rect.x - SAFE_DIST - bw;
        const cLeft = bLeft - GAP - cw;
        const topY  = elCy - BUBBLE_H / 2;

        if (cLeft < PAD) {
            // Not enough room → stack char below bubble
            const bL = clampX(bLeft);
            const bT = clampY(topY);
            charStyle   = { position: 'fixed', zIndex: 9998, left: clampCX(bL + bw / 2 - cw / 2), top: clampCY(bT + BUBBLE_H + GAP), width: cw, height: ch, opacity: 1, transition: POSITION_TRANS };
            bubbleStyle = { position: 'fixed', zIndex: 9999, left: bL, top: bT, opacity: 1, transform: 'none', transition: 'all 0.4s ease' };
            tailSide    = 'right';
        } else {
            charStyle   = { position: 'fixed', zIndex: 9998, left: clampCX(cLeft), top: clampCY(elCy - ch / 2), width: cw, height: ch, opacity: 1, transition: POSITION_TRANS };
            bubbleStyle = { position: 'fixed', zIndex: 9999, left: clampX(bLeft), top: clampY(topY), opacity: 1, transform: 'none', transition: 'all 0.4s ease' };
            tailSide    = 'left'; // bubble tail on left → points toward char (which is left of bubble)
        }

    } else if (side === 'bottom') {
        // Below element: [char] [GAP] [bubble] — centered under element
        const groupW   = cw + GAP + bw;
        const groupLeft = Math.max(PAD, Math.min(vw - groupW - PAD, elCx - groupW / 2));
        const groupTop  = rect.bottom + SAFE_DIST;

        charStyle   = { position: 'fixed', zIndex: 9998, left: groupLeft, top: clampCY(groupTop + Math.max(0, (BUBBLE_H - ch) / 2)), width: cw, height: ch, opacity: 1, transition: POSITION_TRANS };
        bubbleStyle = { position: 'fixed', zIndex: 9999, left: clampX(groupLeft + cw + GAP), top: clampY(groupTop), opacity: 1, transform: 'none', transition: 'all 0.4s ease' };
        tailSide    = 'left'; // bubble tail on left → points toward char

    } else {
        // Above element: [char] [GAP] [bubble] — centered above element
        const groupW    = cw + GAP + bw;
        const groupLeft = Math.max(PAD, Math.min(vw - groupW - PAD, elCx - groupW / 2));
        const bTop      = rect.y - SAFE_DIST - BUBBLE_H;

        if (bTop < PAD) {
            // Fallback to bottom
            const groupTop = rect.bottom + SAFE_DIST;
            charStyle   = { position: 'fixed', zIndex: 9998, left: groupLeft, top: clampCY(groupTop + Math.max(0, (BUBBLE_H - ch) / 2)), width: cw, height: ch, opacity: 1, transition: POSITION_TRANS };
            bubbleStyle = { position: 'fixed', zIndex: 9999, left: clampX(groupLeft + cw + GAP), top: clampY(groupTop), opacity: 1, transform: 'none', transition: 'all 0.4s ease' };
            tailSide    = 'left';
        } else {
            charStyle   = { position: 'fixed', zIndex: 9998, left: groupLeft, top: clampCY(bTop + Math.max(0, (BUBBLE_H - ch) / 2)), width: cw, height: ch, opacity: 1, transition: POSITION_TRANS };
            bubbleStyle = { position: 'fixed', zIndex: 9999, left: clampX(groupLeft + cw + GAP), top: clampY(bTop), opacity: 1, transform: 'none', transition: 'all 0.4s ease' };
            tailSide    = 'left';
        }
    }

    return { charStyle, bubbleStyle, tailSide, bw };
}

/* ─── Main component ─── */
export default function DashboardTour({ userId = '' }) {
    const TOUR_DONE_KEY = userId ? `${TOUR_DONE_PREFIX}_${userId}` : TOUR_DONE_PREFIX;
    const [tourActive, setTourActive] = useState(false);
    const [stepIdx, setStepIdx]       = useState(0);
    const [exiting, setExiting]       = useState(false);
    const [showChat, setShowChat]     = useState(false);
    const [isSpeaking, setIsSpeaking] = useState(true);
    const [vp, setVp]                 = useState({ w: window.innerWidth, h: window.innerHeight });

    const currentStep = TOUR_STEPS[stepIdx];

    /* Track viewport size */
    useEffect(() => {
        const handleResize = () => setVp({ w: window.innerWidth, h: window.innerHeight });
        window.addEventListener('resize', handleResize);
        return () => window.removeEventListener('resize', handleResize);
    }, []);

    /* Auto-start tour */
    useEffect(() => {
        if (localStorage.getItem(TOUR_DONE_KEY)) { setShowChat(true); return; }
        const t = setTimeout(() => setTourActive(true), TOUR_START_DELAY);
        return () => clearTimeout(t);
    }, [TOUR_DONE_KEY]);

    /* Speaking state: true whenever step changes */
    useEffect(() => {
        if (!tourActive) return;
        setIsSpeaking(true);
        const t = setTimeout(() => setIsSpeaking(false), 2400);
        return () => clearTimeout(t);
    }, [stepIdx, tourActive]);

    /* Measure highlighted element */
    const elementRect = useMeasuredRect(currentStep.selector, tourActive && !exiting);

    /* Compute layout */
    const { charStyle, bubbleStyle, tailSide, bw } = computeLayout(
        elementRect, exiting, vp.w, vp.h
    );

    const handleNext   = useCallback(() => { if (stepIdx < TOTAL_STEPS - 1) setStepIdx((i) => i + 1); }, [stepIdx]);
    const handleBack   = useCallback(() => { if (stepIdx > 0) setStepIdx((i) => i - 1); }, [stepIdx]);

    const completeTour = useCallback(() => {
        setExiting(true);
        localStorage.setItem(TOUR_DONE_KEY, 'true');
        setTimeout(() => { setTourActive(false); setExiting(false); setShowChat(true); }, EXIT_ANIM_DURATION);
    }, [TOUR_DONE_KEY]);

    const handleSkip   = useCallback(() => completeTour(), [completeTour]);
    const handleFinish = useCallback(() => completeTour(), [completeTour]);

    if (tourActive) {
        return (
            <>
                {/* Float + transition keyframes */}
                <style>{`
                    @keyframes tourCharFloat {
                        0%   { transform: translateY(0px);  }
                        50%  { transform: translateY(-6px); }
                        100% { transform: translateY(0px);  }
                    }
                `}</style>

                <HighlightOverlay
                    targetSelector={currentStep.selector}
                    active={!exiting}
                />

                {/* Character — outer div handles position transitions, inner div floats */}
                <div style={charStyle}>
                    <div style={{
                        width: '100%', height: '100%',
                        animation: exiting ? 'none' : 'tourCharFloat 2.4s ease-in-out infinite',
                    }}>
                        <Suspense fallback={null}>
                            <TourAssistantCharacter
                                isSpeaking={isSpeaking}
                                animationState={currentStep.characterAnim || 'idle'}
                            />
                        </Suspense>
                    </div>
                </div>

                {/* Speech bubble */}
                <div style={bubbleStyle}>
                    <TourSpeechBubble
                        text={currentStep.text}
                        stepIndex={stepIdx}
                        totalSteps={TOTAL_STEPS}
                        onNext={handleNext}
                        onBack={handleBack}
                        onSkip={handleSkip}
                        onFinish={handleFinish}
                        isLast={stepIdx === TOTAL_STEPS - 1}
                        tailSide={tailSide}
                        width={bw}
                    />
                </div>

                {/* Pointer-events blocker (above overlay, below tour UI) */}
                <div style={{ position: 'fixed', inset: 0, zIndex: 9989, cursor: 'default' }} />
            </>
        );
    }

    return <AssistantChatWidget visible={showChat} />;
}
