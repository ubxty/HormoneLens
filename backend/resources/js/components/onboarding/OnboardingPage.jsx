import React, { useState, useCallback, useEffect, Suspense } from 'react';
import AssistantCharacter from './AssistantCharacter';
import SpeechBubble from './SpeechBubble';
import ProgressBar from './ProgressBar';
import HealthQuestionForm, { STEPS } from './HealthQuestionForm';

/* ─── intro messages the character says before questions ─── */
const INTRO_MESSAGES = [
    "Hi there! I'm Luna — your personal health assistant.",
    "I'll walk you through setting up your health profile so we can give you the best insights.",
    "Let's get started! I'll ask you a few quick questions.",
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

/* ──────────────────────────────────────────────── */
export default function OnboardingPage() {
    const [phase, setPhase]           = useState(PHASE.INTRO);
    const [stepIdx, setStepIdx]       = useState(0);
    const [answers, setAnswers]       = useState({});
    const [error, setError]           = useState(null);
    const [isSpeaking, setIsSpeaking] = useState(true);
    const [submitErr, setSubmitErr]   = useState(null);

    /* Current bubble messages */
    const bubbleMessages =
        phase === PHASE.INTRO    ? INTRO_MESSAGES :
        phase === PHASE.DONE     ? ["All done! Redirecting you to your dashboard…"] :
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
        } else {
            /* All answered — submit */
            setPhase(PHASE.SUBMITTING);
            setIsSpeaking(true);

            const payload = {};
            STEPS.forEach((s) => {
                const v = answers[s.key];
                if (v !== undefined && v !== '') payload[s.key] = v;
            });

            submitHealthProfile(payload)
                .then(() => {
                    setPhase(PHASE.DONE);
                    setIsSpeaking(true);
                    setTimeout(() => { window.location.href = '/dashboard'; }, 2500);
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
            background: 'linear-gradient(135deg, #f5f3ff 0%, #ede9fe 50%, #f0e7fe 100%)',
            display: 'flex', flexDirection: 'column', alignItems: 'center',
            padding: '24px 16px',
            fontFamily: 'system-ui, -apple-system, sans-serif',
        }}>
            {/* Header */}
            <h1 style={{
                fontSize: 28, fontWeight: 700, color: '#4c1d95',
                margin: '0 0 4px', letterSpacing: '-0.3px',
            }}>
                Welcome to HormoneLens
            </h1>
            <p style={{ fontSize: 14, color: '#7c3aed', margin: '0 0 20px' }}>
                Let's set up your health profile
            </p>

            {/* Progress bar (only during questions) */}
            {showForm && <ProgressBar current={stepIdx + 1} total={TOTAL_STEPS} />}

            {/* Main content area */}
            <div style={{
                display: 'flex', alignItems: 'flex-start', justifyContent: 'center',
                gap: 24, flexWrap: 'wrap', marginTop: 24, width: '100%', maxWidth: 900,
            }}>
                {/* 3D Character */}
                <div style={{ width: 280, height: 360, flexShrink: 0 }}>
                    <Suspense fallback={
                        <div style={{
                            width: '100%', height: '100%', display: 'flex',
                            alignItems: 'center', justifyContent: 'center',
                            color: '#7c3aed', fontSize: 14,
                        }}>Loading character…</div>
                    }>
                        <AssistantCharacter isSpeaking={isSpeaking} />
                    </Suspense>
                </div>

                {/* Speech + Form column */}
                <div style={{
                    flex: 1, minWidth: 300, maxWidth: 460,
                    display: 'flex', flexDirection: 'column', gap: 20,
                }}>
                    <SpeechBubble
                        key={phase === PHASE.INTRO ? 'intro' : phase === PHASE.DONE ? 'done' : `step-${stepIdx}`}
                        messages={bubbleMessages}
                        onDone={handleBubbleDone}
                    />

                    {/* Question form */}
                    {showForm && (
                        <div style={{
                            background: '#fff', borderRadius: 16,
                            padding: '24px 28px',
                            boxShadow: '0 2px 16px rgba(124,58,237,0.06)',
                            border: '1px solid #ede9fe',
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
                                        padding: '10px 22px', borderRadius: 10,
                                        border: '1.5px solid #e2ddf5', background: '#fff',
                                        color: '#6d28d9', fontSize: 14, fontWeight: 600,
                                        cursor: 'pointer',
                                    }}>
                                        Back
                                    </button>
                                )}
                                <button onClick={handleNext} style={{
                                    padding: '10px 28px', borderRadius: 10,
                                    border: 'none',
                                    background: 'linear-gradient(135deg, #7c3aed, #6d28d9)',
                                    color: '#fff', fontSize: 14, fontWeight: 600,
                                    cursor: 'pointer',
                                    boxShadow: '0 2px 8px rgba(124,58,237,0.3)',
                                }}>
                                    {stepIdx + 1 < TOTAL_STEPS ? 'Next' : 'Finish'}
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
                            }}
                        >
                            Skip for now — I'll fill this in later
                        </a>
                    )}
                </div>
            </div>
        </div>
    );
}
