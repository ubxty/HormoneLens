import React, { useState, useRef, useEffect, useCallback } from 'react';

/* ─────────────────────────────────────────────────────────
   KNOWLEDGE BASE
   Each entry has:
     patterns  – keywords to match
     reply     – friendly text
     actions   – optional [{label, href}] buttons
   ───────────────────────────────────────────────────────── */
const KNOWLEDGE = [
    {
        patterns: ['twin', 'digital twin', 'create twin', 'generate twin', 'body model', 'my twin'],
        reply: "Great! I can help you create your Digital Twin. 🧬\n\nJust click the button below and we'll generate it for you.",
        actions: [{ label: '✨ Create My Digital Twin', href: '/digital-twin' }],
    },
    {
        patterns: ['simulation', 'simulate', 'run simulation', 'predict', 'metabolic', 'risk predict'],
        reply: "You can run a metabolic simulation to see how lifestyle changes affect your hormone health. 💡\n\nClick below to get started!",
        actions: [{ label: '⚡ Open Simulations', href: '/simulations' }],
    },
    {
        patterns: ['food', 'diet', 'eat', 'nutrition', 'meal', 'what to eat', 'diet suggestion'],
        reply: "Let's see how different foods affect your hormonal balance. 🥗\n\nThe Food Impact page shows you exactly which foods help or hurt your scores.",
        actions: [{ label: '🍛 Check Food Impact', href: '/food-impact' }],
    },
    {
        patterns: ['history', 'past', 'previous', 'track', 'old result', 'log'],
        reply: "Your full simulation history is saved so you can track improvements over time. 📈",
        actions: [{ label: '🕐 View History', href: '/history' }],
    },
    {
        patterns: ['insight', 'health insight', 'analyse', 'analyze', 'overview', 'summary'],
        reply: "Your health insights give you a full picture of how your hormones are doing. Head to your dashboard for a detailed overview! 📊",
        actions: [{ label: '📊 View Health Insights', href: '/dashboard' }],
    },
    {
        patterns: ['profile', 'setting', 'update', 'change', 'edit profile', 'personal info'],
        reply: "You can update your health profile anytime to keep your Digital Twin accurate. 👤\n\nKeep it current for the best predictions!",
        actions: [{ label: '👤 Update My Profile', href: '/health-profile' }],
    },
    {
        patterns: ['score', 'metric', 'number', 'what does', 'mean', 'reading'],
        reply: "Each of your scores (Stress, Sleep, Insulin, Metabolic, Diet) goes from 0–10.\n\n🟢 Low  🟡 Moderate  🔴 High risk\n\nFocus on improving the highest scores first — small changes make a big difference!",
        actions: [{ label: '📊 See My Scores', href: '/dashboard' }],
    },
    {
        patterns: ['tip', 'advice', 'recommend', 'improve', 'what should i'],
        reply: "Here are some quick wins for hormone health:\n\n• 🛏️ Aim for 7–8 hours of sleep\n• 💧 Drink 2+ litres of water daily\n• 🧘 Manage stress — even a 10‑min walk helps\n• 🏃 Regular exercise balances hormones\n\nYou're doing great by being here! 💪",
    },
    {
        patterns: ['hello', 'hi', 'hey', 'morning', 'afternoon', 'evening', 'how are you'],
        reply: "Hey there! 👋 I'm Luna, your HormoneLens guide.\n\nI'm here to help you understand your health data and get the most from the platform. What would you like to do?",
        actions: [
            { label: '✨ Create My Digital Twin', href: '/digital-twin' },
            { label: '⚡ Run a Simulation', href: '/simulations' },
        ],
    },
    {
        patterns: ['thank', 'thanks', 'great', 'awesome', 'nice', 'perfect', 'cool'],
        reply: "You're so welcome! 😊 You're doing great!\n\nLet me know if there's anything else I can help you with.",
    },
    {
        patterns: ['help', 'what can you do', 'options', 'features', 'guide', 'tour'],
        reply: "I can help you with lots of things here! 🤩\n\nTry asking me about:",
        actions: [
            { label: '✨ Create My Digital Twin', href: '/digital-twin' },
            { label: '⚡ Run a Simulation', href: '/simulations' },
            { label: '📊 My Health Insights', href: '/dashboard' },
            { label: '🍛 Improve My Diet', href: '/food-impact' },
        ],
    },
];

const DEFAULT_REPLY = "Hmm, I'm not completely sure about that yet — but I'm always learning! 🤔\n\nTry asking me about one of these:";
const DEFAULT_ACTIONS = [
    { label: '✨ Create My Digital Twin', href: '/digital-twin' },
    { label: '⚡ Run a Simulation', href: '/simulations' },
    { label: '📊 My Health Insights', href: '/dashboard' },
    { label: '🍛 Improve My Diet', href: '/food-impact' },
];

function getResponse(userMsg) {
    const lower = userMsg.toLowerCase();
    for (const entry of KNOWLEDGE) {
        if (entry.patterns.some((p) => lower.includes(p))) {
            return { reply: entry.reply, actions: entry.actions || [] };
        }
    }
    return { reply: DEFAULT_REPLY, actions: DEFAULT_ACTIONS };
}

/* ─── Quick suggestion chips shown on open ─── */
const SUGGESTIONS = [
    { label: '✨ Create my Digital Twin', prompt: 'I want to create my digital twin' },
    { label: '⚡ Run a Simulation',       prompt: 'I want to run a simulation' },
    { label: '📊 My Health Insights',    prompt: 'Show me my health insights' },
    { label: '🍛 Improve my Diet',       prompt: 'I want diet suggestions' },
];

/* ─── Unique message ids ─── */
let msgCounter = 0;
const uid = () => ++msgCounter;

/* ─── Single message bubble ─── */
function MessageBubble({ msg }) {
    return (
        <div style={{
            alignSelf: msg.role === 'user' ? 'flex-end' : 'flex-start',
            maxWidth: '86%',
            animation: 'msgFadeIn 0.26s cubic-bezier(.4,0,.2,1)',
        }}>
            <div style={{
                padding: '10px 14px',
                borderRadius: msg.role === 'user' ? '16px 16px 4px 16px' : '16px 16px 16px 4px',
                background: msg.role === 'user'
                    ? 'linear-gradient(135deg, #7c3aed, #6d28d9)'
                    : '#fff',
                color: msg.role === 'user' ? '#fff' : '#1e1b2e',
                fontSize: 13.5,
                lineHeight: 1.6,
                whiteSpace: 'pre-wrap',
                boxShadow: msg.role === 'user'
                    ? '0 2px 10px rgba(124,58,237,0.22)'
                    : '0 1px 6px rgba(0,0,0,0.06)',
                border: msg.role === 'user' ? 'none' : '1px solid #ede9fe',
            }}>
                {msg.text}
            </div>

            {/* Action buttons */}
            {msg.actions && msg.actions.length > 0 && (
                <div style={{ display: 'flex', flexDirection: 'column', gap: 7, marginTop: 8 }}>
                    {msg.actions.map((a, i) => (
                        <a
                            key={i}
                            href={a.href}
                            style={{
                                display: 'block',
                                padding: '9px 16px',
                                borderRadius: 12,
                                border: '1.5px solid #7c3aed',
                                background: 'linear-gradient(135deg, rgba(124,58,237,0.07), rgba(167,139,250,0.07))',
                                color: '#6d28d9',
                                fontSize: 13, fontWeight: 700,
                                textDecoration: 'none',
                                textAlign: 'center',
                                transition: 'background 0.2s, transform 0.15s, box-shadow 0.2s',
                                fontFamily: 'system-ui, -apple-system, sans-serif',
                                cursor: 'pointer',
                            }}
                            onMouseEnter={(e) => {
                                e.currentTarget.style.background = 'linear-gradient(135deg, #7c3aed, #6d28d9)';
                                e.currentTarget.style.color = '#fff';
                                e.currentTarget.style.transform = 'scale(1.02)';
                                e.currentTarget.style.boxShadow = '0 4px 14px rgba(124,58,237,0.28)';
                            }}
                            onMouseLeave={(e) => {
                                e.currentTarget.style.background = 'linear-gradient(135deg, rgba(124,58,237,0.07), rgba(167,139,250,0.07))';
                                e.currentTarget.style.color = '#6d28d9';
                                e.currentTarget.style.transform = 'scale(1)';
                                e.currentTarget.style.boxShadow = 'none';
                            }}
                        >
                            {a.label}
                        </a>
                    ))}
                </div>
            )}
        </div>
    );
}

/* ─── Main component ─── */
export default function ChatWindow({ onClose }) {
    const [messages, setMessages] = useState([
        {
            id: uid(), role: 'assistant',
            text: "Hey! 👋 I'm Luna — your HormoneLens guide.\n\nHow can I help you today?",
            actions: [],
        },
    ]);
    const [input, setInput]       = useState('');
    const [isTyping, setIsTyping] = useState(false);
    const scrollRef               = useRef(null);

    /* Auto-scroll whenever messages or typing state change */
    useEffect(() => {
        const el = scrollRef.current;
        if (el) el.scrollTo({ top: el.scrollHeight, behavior: 'smooth' });
    }, [messages, isTyping]);

    const sendMessage = useCallback((text) => {
        const trimmed = text.trim();
        if (!trimmed || isTyping) return;

        setMessages((ms) => [...ms, { id: uid(), role: 'user', text: trimmed }]);
        setInput('');
        setIsTyping(true);

        const delay = 700 + Math.random() * 500;
        setTimeout(() => {
            const { reply, actions } = getResponse(trimmed);
            setMessages((ms) => [...ms, { id: uid(), role: 'assistant', text: reply, actions }]);
            setIsTyping(false);
        }, delay);
    }, [isTyping]);

    const handleKey = (e) => {
        if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMessage(input); }
    };

    return (
        <div style={{
            width: 360, height: 520,
            display: 'flex', flexDirection: 'column',
            background: '#fff',
            borderRadius: 20,
            boxShadow: '0 16px 56px rgba(124,58,237,0.20), 0 4px 16px rgba(0,0,0,0.08)',
            border: '1px solid #ede9fe',
            overflow: 'hidden',
            fontFamily: 'system-ui, -apple-system, sans-serif',
        }}>
            {/* ── Header ── */}
            <div style={{
                display: 'flex', alignItems: 'center', gap: 10,
                padding: '14px 16px',
                background: 'linear-gradient(135deg, #7c3aed, #6d28d9)',
                color: '#fff',
                flexShrink: 0,
            }}>
                <div style={{
                    width: 36, height: 36, borderRadius: '50%',
                    background: 'rgba(255,255,255,0.22)',
                    display: 'flex', alignItems: 'center', justifyContent: 'center',
                    fontSize: 18,
                }}>🌸</div>
                <div style={{ flex: 1 }}>
                    <div style={{ fontSize: 14, fontWeight: 700 }}>Luna</div>
                    <div style={{ fontSize: 11, opacity: 0.8 }}>Your HormoneLens Guide</div>
                </div>
                <button onClick={onClose} style={{
                    background: 'rgba(255,255,255,0.15)',
                    border: 'none', borderRadius: 8,
                    color: '#fff', width: 30, height: 30,
                    fontSize: 16, cursor: 'pointer',
                    display: 'flex', alignItems: 'center', justifyContent: 'center',
                }}>✕</button>
            </div>

            {/* ── Quick suggestions (shown before any user message) ── */}
            {messages.length === 1 && (
                <div style={{
                    padding: '12px 14px 4px',
                    background: '#faf9ff',
                    borderBottom: '1px solid #f3f0ff',
                    flexShrink: 0,
                }}>
                    <p style={{ fontSize: 11.5, color: '#9ca3af', margin: '0 0 8px', fontWeight: 600, letterSpacing: 0.3 }}>
                        QUICK SUGGESTIONS
                    </p>
                    <div style={{ display: 'flex', flexWrap: 'wrap', gap: 7 }}>
                        {SUGGESTIONS.map((s) => (
                            <button
                                key={s.label}
                                onClick={() => sendMessage(s.prompt)}
                                style={{
                                    padding: '6px 12px',
                                    borderRadius: 20,
                                    border: '1.5px solid #c4b5fd',
                                    background: '#f5f3ff',
                                    color: '#6d28d9',
                                    fontSize: 12, fontWeight: 600,
                                    cursor: 'pointer',
                                    fontFamily: 'inherit',
                                    transition: 'background 0.2s, border-color 0.2s',
                                    lineHeight: 1.4,
                                }}
                                onMouseEnter={(e) => {
                                    e.currentTarget.style.background = '#ede9fe';
                                    e.currentTarget.style.borderColor = '#7c3aed';
                                }}
                                onMouseLeave={(e) => {
                                    e.currentTarget.style.background = '#f5f3ff';
                                    e.currentTarget.style.borderColor = '#c4b5fd';
                                }}
                            >
                                {s.label}
                            </button>
                        ))}
                    </div>
                </div>
            )}

            {/* ── Message list ── */}
            <div
                ref={scrollRef}
                style={{
                    flex: 1, overflowY: 'auto', padding: '14px 14px 8px',
                    display: 'flex', flexDirection: 'column', gap: 12,
                    background: '#faf9ff',
                }}
            >
                {messages.map((msg) => (
                    <MessageBubble key={msg.id} msg={msg} />
                ))}

                {/* Typing indicator */}
                {isTyping && (
                    <div style={{
                        alignSelf: 'flex-start',
                        animation: 'msgFadeIn 0.2s ease',
                    }}>
                        <div style={{
                            padding: '10px 16px',
                            borderRadius: '16px 16px 16px 4px',
                            background: '#fff',
                            border: '1px solid #ede9fe',
                            display: 'flex', gap: 5, alignItems: 'center',
                            boxShadow: '0 1px 6px rgba(0,0,0,0.06)',
                        }}>
                            <span style={{ fontSize: 11, color: '#9ca3af', marginRight: 4 }}>Luna is typing</span>
                            {[0, 0.2, 0.4].map((delay, i) => (
                                <span key={i} style={{
                                    width: 6, height: 6, borderRadius: '50%',
                                    background: '#a78bfa',
                                    display: 'inline-block',
                                    animation: `chatDot 1.1s ease-in-out ${delay}s infinite`,
                                }} />
                            ))}
                        </div>
                    </div>
                )}
            </div>

            {/* ── Input bar ── */}
            <div style={{
                display: 'flex', gap: 8,
                padding: '10px 12px',
                borderTop: '1px solid #ede9fe',
                background: '#fff',
                flexShrink: 0,
            }}>
                <input
                    type="text"
                    placeholder="Ask Luna anything…"
                    value={input}
                    onChange={(e) => setInput(e.target.value)}
                    onKeyDown={handleKey}
                    disabled={isTyping}
                    style={{
                        flex: 1, padding: '10px 14px',
                        borderRadius: 12,
                        border: '1.5px solid #e2ddf5',
                        fontSize: 13.5, outline: 'none',
                        fontFamily: 'inherit',
                        background: '#faf9ff',
                        transition: 'border-color 0.2s',
                        opacity: isTyping ? 0.6 : 1,
                    }}
                    onFocus={(e) => { e.currentTarget.style.borderColor = '#7c3aed'; }}
                    onBlur={(e) => { e.currentTarget.style.borderColor = '#e2ddf5'; }}
                />
                <button
                    onClick={() => sendMessage(input)}
                    disabled={isTyping || !input.trim()}
                    style={{
                        width: 40, height: 40, borderRadius: 12,
                        border: 'none',
                        background: 'linear-gradient(135deg, #7c3aed, #6d28d9)',
                        color: '#fff', fontSize: 16,
                        cursor: isTyping || !input.trim() ? 'not-allowed' : 'pointer',
                        display: 'flex', alignItems: 'center', justifyContent: 'center',
                        boxShadow: '0 2px 8px rgba(124,58,237,0.25)',
                        flexShrink: 0,
                        opacity: isTyping || !input.trim() ? 0.55 : 1,
                        transition: 'opacity 0.2s',
                    }}
                >↑</button>
            </div>

            <style>{`
                @keyframes chatDot {
                    0%,60%,100% { opacity: 0.3; transform: translateY(0); }
                    30%         { opacity: 1;   transform: translateY(-4px); }
                }
                @keyframes msgFadeIn {
                    from { opacity: 0; transform: translateY(8px); }
                    to   { opacity: 1; transform: translateY(0); }
                }
            `}</style>
        </div>
    );
}
