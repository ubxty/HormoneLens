import React, { useState, useEffect, useCallback, Suspense } from 'react';
import HighlightOverlay from './HighlightOverlay';
import TourSpeechBubble from './TourSpeechBubble';
import TourAssistantCharacter from './TourAssistantCharacter';
import AssistantChatWidget from './AssistantChatWidget';

/* ─── Storage key ─── */
const TOUR_DONE_PREFIX = 'hormonelens_tour_completed';

/* ─── Tour step definitions ─── */
const TOUR_STEPS = [
    {
        id: 'welcome',
        selector: null,
        text: 'Welcome to your HormoneLens dashboard! Let me quickly walk you through the features.',
    },
    {
        id: 'risk-prediction',
        selector: '[data-tour-id="score-cards"]',
        text: 'This is your hormone risk prediction area where you can simulate your health factors.',
    },
    {
        id: 'health-metrics',
        selector: '[data-tour-id="twin-header"]',
        text: 'Here you can quickly view your key health metrics and hormonal indicators.',
    },
    {
        id: 'historical-trends',
        selector: '[data-tour-id="body-map"]',
        text: 'These charts show how your hormonal health changes over time.',
    },
    {
        id: 'simulation-history',
        selector: '[data-tour-id="nav-simulations"]',
        text: 'This section stores all your past simulations so you can track improvements.',
    },
    {
        id: 'ai-recommendations',
        selector: '[data-tour-id="nav-knowledge"]',
        text: 'Here the AI suggests lifestyle adjustments based on your hormonal profile.',
    },
    {
        id: 'profile-settings',
        selector: '[data-tour-id="nav-health-profile"]',
        text: 'You can update your health profile and personal details here.',
    },
    {
        id: 'finish',
        selector: null,
        text: "That's your HormoneLens dashboard! I'm always here if you need help.",
    },
];

const TOTAL_STEPS = TOUR_STEPS.length;

/* small delay before starting the tour so dashboard can paint */
const TOUR_START_DELAY = 1200;
/* duration for the exit/move animation of the character */
const EXIT_ANIM_DURATION = 800;

/* ─── Main component ─── */
export default function DashboardTour({ userId = '' }) {
    const TOUR_DONE_KEY = userId ? `${TOUR_DONE_PREFIX}_${userId}` : TOUR_DONE_PREFIX;
    const [tourActive, setTourActive] = useState(false);
    const [stepIdx, setStepIdx]       = useState(0);
    const [exiting, setExiting]       = useState(false);
    const [showChat, setShowChat]     = useState(false);
    const [isSpeaking, setIsSpeaking] = useState(true);

    /* Auto-start if tour hasn't been completed */
    useEffect(() => {
        if (localStorage.getItem(TOUR_DONE_KEY)) {
            setShowChat(true);
            return;
        }
        const timer = setTimeout(() => setTourActive(true), TOUR_START_DELAY);
        return () => clearTimeout(timer);
    }, [TOUR_DONE_KEY]);

    /* Speaking state: true whenever step changes */
    useEffect(() => {
        if (!tourActive) return;
        setIsSpeaking(true);
        const t = setTimeout(() => setIsSpeaking(false), 2400);
        return () => clearTimeout(t);
    }, [stepIdx, tourActive]);

    /* ── Handlers ── */
    const handleNext = useCallback(() => {
        if (stepIdx < TOTAL_STEPS - 1) setStepIdx((i) => i + 1);
    }, [stepIdx]);

    const handleBack = useCallback(() => {
        if (stepIdx > 0) setStepIdx((i) => i - 1);
    }, [stepIdx]);

    const completeTour = useCallback(() => {
        setExiting(true);
        localStorage.setItem(TOUR_DONE_KEY, 'true');
        setTimeout(() => {
            setTourActive(false);
            setExiting(false);
            setShowChat(true);
        }, EXIT_ANIM_DURATION);
    }, [TOUR_DONE_KEY]);

    const handleSkip = useCallback(() => {
        completeTour();
    }, [completeTour]);

    const handleFinish = useCallback(() => {
        completeTour();
    }, [completeTour]);

    const currentStep = TOUR_STEPS[stepIdx];

    /* ── Tour UI ── */
    if (tourActive) {
        /* Compute character + bubble position — right side, vertically centred */
        const characterStyle = {
            position: 'fixed',
            right: 20,
            bottom: exiting ? 24 : 'calc(50% - 180px)',
            width: exiting ? 64 : 200,
            height: exiting ? 64 : 340,
            zIndex: 9998,
            transition: `all ${EXIT_ANIM_DURATION}ms cubic-bezier(.4,0,.2,1)`,
            ...(exiting ? {
                opacity: 0,
                borderRadius: '50%',
                overflow: 'hidden',
            } : {}),
        };

        const bubbleStyle = {
            position: 'fixed',
            right: 230,
            top: 'calc(50% - 100px)',
            zIndex: 9999,
            opacity: exiting ? 0 : 1,
            transform: exiting ? 'translateX(20px)' : 'translateX(0)',
            transition: `opacity ${EXIT_ANIM_DURATION}ms, transform ${EXIT_ANIM_DURATION}ms`,
        };

        return (
            <>
                <HighlightOverlay
                    targetSelector={currentStep.selector}
                    active={!exiting}
                />

                {/* 3D Character */}
                <div style={characterStyle}>
                    <Suspense fallback={null}>
                        <TourAssistantCharacter isSpeaking={isSpeaking} />
                    </Suspense>
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
                    />
                </div>

                {/* Block pointer events on the rest of the page during tour */}
                <div style={{
                    position: 'fixed', inset: 0, zIndex: 9989,
                    cursor: 'default',
                }} />
            </>
        );
    }

    /* ── Chat widget (post-tour) ── */
    return <AssistantChatWidget visible={showChat} />;
}
