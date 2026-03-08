import React, { useState, useCallback, useEffect, Suspense, useMemo } from 'react';
import AssistantCharacter from './AssistantCharacter';
import SpeechBubble from './SpeechBubble';
import ProgressBar from './ProgressBar';
import HealthQuestionForm, { STEPS } from './HealthQuestionForm';

/* ─── intro messages the character says before questions ─── */
const INTRO_MESSAGES = [
    "Hi there! I'm HormoneLens — your personal health assistant. 👋",
    "I'll walk you through building your very own digital health twin.",
    "Just pick the answers that feel right — no wrong choices here!",
];

/* ─── per-step encouragement ─── */
const STEP_PROMPTS = [
    ["Let's start simple — tell me about yourself."],
    ["Great! Now, what's your weight?"],
    ["And your height?"],
    ["How's your sleep been lately?"],
    ["How about stress — we all have some!"],
    ["How active would you say you are?"],
    ["Staying hydrated is key — how much water do you drink?"],
    ["Do you have any known health conditions?"],
    ["Almost done! Anything about your eating habits you'd like to share?"],
];

const TOTAL_STEPS = STEPS.length;

/* ─── API helper ─── */
function getCsrf() {
    return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
}

async function submitHealthProfile(data) {
    const res = await fetch('/api/health-profile', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': getCsrf(),
        },
        credentials: 'same-origin',
        body: JSON.stringify(data),
    });
    if (!res.ok) {
        const body = await res.json().catch(() => ({}));
        throw new Error(body.message || 'Failed to save health profile.');
    }
    return res.json();
}

/* ─── Phase enum ─── */
const PHASE = { INTRO: 'intro', QUESTIONS: 'questions', SUBMITTING: 'submitting', DONE: 'done' };

/* ─── Animation state from progress ─── */
function getAnimationState(phase, stepIdx, recentlyAdvanced) {
    if (phase === PHASE.INTRO) return 'wave';
    if (phase === PHASE.DONE) return 'spin';
    if (phase === PHASE.SUBMITTING) return 'clap';
    if (recentlyAdvanced) return 'clap';
    const progress = (stepIdx + 1) / TOTAL_STEPS;
    if (progress >= 0.85) return 'clap';
    return 'idle';
}

/* ──────────────────────────────────────────────── */
export default function OnboardingPage() {
    const [phase, setPhase]           = useState(PHASE.INTRO);
    const [stepIdx, setStepIdx]       = useState(0);
    const [answers, setAnswers]       = useState({});
    const [error, setError]           = useState(null);
    const [isSpeaking, setIsSpeaking] = useState(true);
    const [submitErr, setSubmitErr]   = useState(null);
    const [recentlyAdvanced, setRecentlyAdvanced] = useState(false);

    /* Derive animation state */
    const animationState = useMemo(
        () => getAnimationState(phase, stepIdx, recentlyAdvanced),
        [phase, stepIdx, recentlyAdvanced]
    );

    /* Current bubble messages */
    const bubbleMessages =
        phase === PHASE.INTRO      ? INTRO_MESSAGES :
        phase === PHASE.SUBMITTING ? ["Saving your profile — hang on a moment…"] :
        (STEP_PROMPTS[stepIdx] ?? ["Next question!"]);

    /* When bubble finishes typing */
    const handleBubbleDone = useCallback(() => {
        setIsSpeaking(false);
        if (phase === PHASE.INTRO) setPhase(PHASE.QUESTIONS);
    }, [phase]);

    /* Validate & advance step */
    const handleNext = useCallback(() => {
        const step = STEPS[stepIdx];
        const val = answers[step.key] ?? '';
        const err = step.validate(val);
        if (err) { setError(err); return; }
        setError(null);

        if (stepIdx + 1 < TOTAL_STEPS) {
            setStepIdx((i) => i + 1);
            setIsSpeaking(true);
            setRecentlyAdvanced(true);
            setTimeout(() => setRecentlyAdvanced(false), 2500);
        } else {
            setPhase(PHASE.SUBMITTING);
            setIsSpeaking(true);

            const payload = {};
            STEPS.forEach((s) => {
                const v = answers[s.key];
                if (v === undefined || v === '') return;
                if (s.key === 'height' && typeof v === 'object') {
                    payload.height = Math.round((Number(v.feet) * 12 + Number(v.inches)) * 2.54);
                } else if (s.key === 'disease_type' && typeof v === 'object') {
                    if (!v.hasCondition) {
                        payload.disease_type = 'None';
                    } else {
                        payload.disease_type = v.condition === 'Other' ? (v.otherText || 'Other') : v.condition;
                    }
                } else {
                    payload[s.key] = v;
                }
            });

            submitHealthProfile(payload)
                .then(() => {
                    setPhase(PHASE.DONE);
                    setIsSpeaking(true);
                    setTimeout(() => { window.location.href = '/dashboard'; }, 3800);
                })
                .catch((e) => {
                    setSubmitErr(e.message);
                    setPhase(PHASE.QUESTIONS);
                    setIsSpeaking(false);
                });
        }
    }, [stepIdx, answers]);

    /* Go back one step */
    const handleBack = useCallback(() => {
        if (stepIdx > 0) {
            setStepIdx((i) => i - 1);
            setError(null);
            setIsSpeaking(true);
        }
    }, [stepIdx]);

    /* Handle enter key on inputs */
    useEffect(() => {
        const onKey = (e) => {
            if (e.key === 'Enter' && phase === PHASE.QUESTIONS) handleNext();
        };
        window.addEventListener('keydown', onKey);
        return () => window.removeEventListener('keydown', onKey);
    }, [handleNext, phase]);

    const showForm = phase === PHASE.QUESTIONS;

    return (
        <div style={{
            minHeight: '100vh',
            background: 'linear-gradient(135deg, #f5f3ff 0%, #ede9fe 40%, #e0d4f5 100%)',
            display: 'flex',
            flexDirection: 'column',
            fontFamily: 'system-ui, -apple-system, sans-serif',
            overflow: 'hidden',
            position: 'relative',
        }}>
            {/* Floating particles */}
            <div style={{
                position: 'absolute', width: 260, height: 260, borderRadius: '50%',
                background: 'linear-gradient(135deg, #a78bfa, #818cf8)', filter: 'blur(80px)',
                top: -60, left: -60, opacity: 0.15, animation: 'floatParticle 10s ease-in-out infinite',
                pointerEvents: 'none',
            }} />
            <div style={{
                position: 'absolute', width: 200, height: 200, borderRadius: '50%',
                background: 'linear-gradient(135deg, #6366f1, #c084fc)', filter: 'blur(80px)',
                bottom: -40, right: -40, opacity: 0.15, animation: 'floatParticle 13s ease-in-out 2s infinite',
                pointerEvents: 'none',
            }} />

            {/* Header */}
            <div style={{ textAlign: 'center', padding: '24px 32px 8px', position: 'relative', zIndex: 1 }}>
                <h1 style={{
                    fontSize: 26, fontWeight: 800, color: '#4c1d95',
                    margin: '0 0 4px', letterSpacing: '-0.3px',
                    background: 'linear-gradient(135deg, #4c1d95, #7c3aed)',
                    WebkitBackgroundClip: 'text', WebkitTextFillColor: 'transparent',
                    backgroundClip: 'text',
                }}>
                    Welcome to HormoneLens
                </h1>
                <p style={{ fontSize: 13, color: '#7c3aed', margin: 0 }}>
                    Let's build your personal health twin
                </p>
                {showForm && (
                    <div style={{ maxWidth: 480, margin: '12px auto 0' }}>
                        <ProgressBar current={stepIdx + 1} total={TOTAL_STEPS} />
                    </div>
                )}
            </div>

            {/* Main content: Character + Form side by side */}
            <div data-panel="content" style={{
                flex: 1,
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                padding: '0 32px 24px',
                gap: 32,
                position: 'relative',
                zIndex: 1,
            }}>
                {/* 3D Character — main visual element */}
                <div data-panel="character" style={{
                    width: '45%',
                    maxWidth: 520,
                    height: '65vh',
                    minHeight: 420,
                    maxHeight: 680,
                    flexShrink: 0,
                    transition: 'transform 0.5s cubic-bezier(.4,0,.2,1)',
                }}>
                    <Suspense fallback={
                        <div style={{
                            width: '100%', height: '100%', display: 'flex',
                            alignItems: 'center', justifyContent: 'center',
                            color: '#7c3aed', fontSize: 14,
                        }}>Loading character…</div>
                    }>
                        <AssistantCharacter
                            isSpeaking={isSpeaking}
                            animationState={animationState}
                        />
                    </Suspense>
                </div>

                {/* Speech + Form column */}
                <div data-panel="form" style={{
                    flex: 1, minWidth: 280, maxWidth: 480,
                    display: 'flex', flexDirection: 'column', gap: 16,
                    overflowY: 'auto', maxHeight: '80vh',
                    paddingRight: 4,
                }}>
                    <SpeechBubble
                        key={phase === PHASE.INTRO ? 'intro' : `step-${stepIdx}`}
                        messages={bubbleMessages}
                        onDone={handleBubbleDone}
                    />

                    {/* Done state celebration card */}
                    {phase === PHASE.DONE && (
                        <div style={{
                            background: 'rgba(255,255,255,0.92)',
                            backdropFilter: 'blur(16px)',
                            borderRadius: 24,
                            padding: '36px 32px',
                            boxShadow: '0 8px 40px rgba(124,58,237,0.18), 0 2px 0 rgba(255,255,255,0.8) inset',
                            border: '1.5px solid rgba(124,58,237,0.18)',
                            textAlign: 'center',
                            animation: 'doneCardIn 0.5s cubic-bezier(.4,0,.2,1)',
                        }}>
                            <div style={{ fontSize: 54, marginBottom: 14, lineHeight: 1 }}>&#x1F389;</div>
                            <h2 style={{
                                fontSize: 24, fontWeight: 800, margin: '0 0 10px',
                                background: 'linear-gradient(135deg, #4c1d95, #7c3aed)',
                                WebkitBackgroundClip: 'text', WebkitTextFillColor: 'transparent',
                                backgroundClip: 'text',
                            }}>
                                All set!
                            </h2>
                            <p style={{ fontSize: 15, color: '#6d28d9', fontWeight: 600, margin: '0 0 6px' }}>
                                Your health twin is ready.
                            </p>
                            <p style={{ fontSize: 13, color: '#9ca3af', margin: '0 0 24px' }}>
                                Preparing your dashboard…
                            </p>
                            <div style={{ display: 'flex', gap: 10, justifyContent: 'center', alignItems: 'center' }}>
                                {[0, 1, 2].map((i) => (
                                    <div key={i} style={{
                                        width: 11, height: 11, borderRadius: '50%',
                                        background: 'linear-gradient(135deg, #7c3aed, #a78bfa)',
                                        animation: `doneDot 1.3s ease-in-out ${i * 0.22}s infinite`,
                                    }} />
                                ))}
                            </div>
                        </div>
                    )}

                    {/* Question form */}
                    {showForm && (
                        <div style={{
                            background: 'rgba(255,255,255,0.85)',
                            backdropFilter: 'blur(12px)',
                            borderRadius: 20,
                            padding: '24px 28px',
                            boxShadow: '0 4px 24px rgba(124,58,237,0.06), 0 1px 0 rgba(255,255,255,0.6) inset',
                            border: '1px solid rgba(255,255,255,0.5)',
                            transition: 'all 0.3s ease',
                        }}>
                            <HealthQuestionForm
                                stepIndex={stepIdx}
                                value={answers[STEPS[stepIdx].key] ?? ''}
                                onChange={(v) => {
                                    setAnswers((a) => ({ ...a, [STEPS[stepIdx].key]: v }));
                                    if (error) setError(null);
                                }}
                                error={error}
                            />

                            {submitErr && (
                                <p style={{ color: '#ef4444', fontSize: 13, marginTop: 8 }}>
                                    {submitErr}
                                </p>
                            )}

                            {/* Navigation buttons */}
                            <div style={{
                                display: 'flex', gap: 12, marginTop: 20,
                                justifyContent: 'flex-end',
                            }}>
                                {stepIdx > 0 && (
                                    <button onClick={handleBack} style={{
                                        padding: '11px 24px', borderRadius: 12,
                                        border: '2px solid #e2ddf5', background: '#fff',
                                        color: '#6d28d9', fontSize: 14, fontWeight: 600,
                                        cursor: 'pointer',
                                        transition: 'all 0.2s',
                                    }}>
                                        ← Back
                                    </button>
                                )}
                                <button onClick={handleNext} style={{
                                    padding: '11px 32px', borderRadius: 12,
                                    border: 'none',
                                    background: 'linear-gradient(135deg, #7c3aed, #6d28d9)',
                                    color: '#fff', fontSize: 14, fontWeight: 700,
                                    cursor: 'pointer',
                                    boxShadow: '0 4px 16px rgba(124,58,237,0.3)',
                                    transition: 'all 0.2s',
                                }}>
                                    {stepIdx + 1 < TOTAL_STEPS ? 'Continue →' : '✨ Finish'}
                                </button>
                            </div>
                        </div>
                    )}

                    {/* Skip link */}
                    {showForm && (
                        <a
                            href="/dashboard"
                            style={{
                                fontSize: 13, color: '#a78bfa',
                                textDecoration: 'none', textAlign: 'center',
                                transition: 'color 0.2s',
                            }}
                        >
                            Skip for now — I'll fill this in later
                        </a>
                    )}
                </div>
            </div>

            <style>{`
                @keyframes floatParticle {
                    0%, 100% { transform: translate(0,0) scale(1);   opacity: 0.15; }
                    50%      { transform: translate(15px,-20px) scale(1.05); opacity: 0.25; }
                }
                @keyframes doneCardIn {
                    from { opacity: 0; transform: scale(0.94) translateY(14px); }
                    to   { opacity: 1; transform: scale(1) translateY(0); }
                }
                @keyframes doneDot {
                    0%, 80%, 100% { transform: scale(1);   opacity: 0.45; }
                    40%           { transform: scale(1.5); opacity: 1; }
                }
                @media (max-width: 900px) {
                    [data-panel="content"] {
                        flex-direction: column !important;
                        padding: 0 16px 24px !important;
                    }
                    [data-panel="character"] {
                        width: 100% !important;
                        max-width: 100% !important;
                        height: 300px !important;
                        min-height: 260px !important;
                        max-height: 320px !important;
                    }
                    [data-panel="form"] {
                        max-height: none !important;
                        min-width: auto !important;
                        max-width: 100% !important;
                    }
                }
                @media (max-width: 600px) {
                    [data-panel="character"] {
                        height: 240px !important;
                        min-height: 200px !important;
                    }
                }
            `}</style>
        </div>
    );
}
