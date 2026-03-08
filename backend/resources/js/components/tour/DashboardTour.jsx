import React, { useState, useEffect, useCallback, Suspense, useMemo } from 'react';
import HighlightOverlay from './HighlightOverlay';
import TourSpeechBubble from './TourSpeechBubble';
import TourAssistantCharacter from './TourAssistantCharacter2D';
import AssistantChatWidget from './AssistantChatWidget';

/* ─── Storage key ─── */
const TOUR_DONE_PREFIX = 'hormonelens_tour_completed';

/* ─── Tour step definitions with friendly text ─── */
const TOUR_STEPS = [
    {
        id: 'welcome',
        selector: null,
        text: "Hey! I'm HormoneLens, your health buddy. Let me show you around your dashboard!",
        characterAnim: 'wave',
        characterSide: 'right',
    },
    {
        id: 'risk-prediction',
        selector: '[data-tour-id="score-cards"]',
        text: 'These cards show your hormone risk scores. You can see how different areas of your health are doing.',
        characterAnim: 'idle',
        characterSide: 'right',
    },
    {
        id: 'health-metrics',
        selector: '[data-tour-id="twin-header"]',
        text: 'Here are your key health numbers at a glance — like your BMI and overall risk level.',
        characterAnim: 'idle',
        characterSide: 'right',
    },
    {
        id: 'historical-trends',
        selector: '[data-tour-id="body-map"]',
        text: 'This is your body map! Hover over any zone to see detailed hormone insights.',
        characterAnim: 'clap',
        characterSide: 'left',
    },
    {
        id: 'simulation-history',
        selector: '[data-tour-id="nav-simulations"]',
        text: 'Track your past simulations here. See how your health has improved over time!',
        characterAnim: 'idle',
        characterSide: 'left',
    },
    {
        id: 'ai-recommendations',
        selector: '[data-tour-id="nav-knowledge"]',
        text: 'Get personalized lifestyle tips from our AI based on your hormonal profile.',
        characterAnim: 'idle',
        characterSide: 'left',
    },
    {
        id: 'profile-settings',
        selector: '[data-tour-id="nav-health-profile"]',
        text: 'Update your health profile anytime to keep your twin accurate and up-to-date.',
        characterAnim: 'idle',
        characterSide: 'left',
    },
    {
        id: 'finish',
        selector: null,
        text: "That's your HormoneLens dashboard! I'll be right here whenever you need me. Chat with me anytime!",
        characterAnim: 'wave',
        characterSide: 'right',
    },
];

const TOTAL_STEPS = TOUR_STEPS.length;
const TOUR_START_DELAY = 1200;
const EXIT_ANIM_DURATION = 800;

/* ─── Compute character position to avoid overlapping highlights ─── */
function getCharacterPosition(step, exiting) {
    if (exiting) {
        return { right: 20, bottom: 24, width: 64, height: 64, opacity: 0 };
    }
    const side = step.characterSide || 'right';
    if (side === 'left') {
        return { left: 20, bottom: 'calc(50% - 180px)', width: 200, height: 340, opacity: 1 };
    }
    return { right: 20, bottom: 'calc(50% - 180px)', width: 200, height: 340, opacity: 1 };
}

function getBubblePosition(step, exiting) {
    const side = step.characterSide || 'right';
    if (exiting) return { opacity: 0, transform: 'translateX(20px)' };
    if (side === 'left') {
        return { left: 230, top: 'calc(50% - 100px)', opacity: 1, transform: 'translateX(0)' };
    }
    return { right: 230, top: 'calc(50% - 100px)', opacity: 1, transform: 'translateX(0)' };
}

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

    const handleSkip = useCallback(() => { completeTour(); }, [completeTour]);
    const handleFinish = useCallback(() => { completeTour(); }, [completeTour]);

    const currentStep = TOUR_STEPS[stepIdx];
    const charAnim = currentStep.characterAnim || 'idle';

    /* Character and bubble positioning */
    const charPos = useMemo(() => getCharacterPosition(currentStep, exiting), [currentStep, exiting]);
    const bubblePos = useMemo(() => getBubblePosition(currentStep, exiting), [currentStep, exiting]);

    if (tourActive) {
        const characterStyle = {
            position: 'fixed',
            zIndex: 9998,
            transition: `all ${EXIT_ANIM_DURATION}ms cubic-bezier(.4,0,.2,1)`,
            ...charPos,
            ...(exiting ? { borderRadius: '50%', overflow: 'hidden' } : {}),
        };

        const bubbleStyle = {
            position: 'fixed',
            zIndex: 9999,
            transition: `all ${EXIT_ANIM_DURATION}ms cubic-bezier(.4,0,.2,1)`,
            ...bubblePos,
        };

        /* Determine bubble tail direction */
        const bubbleTailSide = currentStep.characterSide === 'left' ? 'left' : 'right';

        return (
            <>
                <HighlightOverlay
                    targetSelector={currentStep.selector}
                    active={!exiting}
                />

                {/* 3D Character */}
                <div style={characterStyle}>
                    <Suspense fallback={null}>
                        <TourAssistantCharacter
                            isSpeaking={isSpeaking}
                            animationState={charAnim}
                        />
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
                        tailSide={bubbleTailSide}
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

    /* Chat widget (post-tour) */
    return <AssistantChatWidget visible={showChat} />;
}
