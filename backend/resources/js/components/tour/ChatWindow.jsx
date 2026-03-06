import React, { useState, useRef, useEffect } from 'react';

/* ─── Pre-built assistant knowledge base ─── */
const KNOWLEDGE = [
    { patterns: ['metric', 'score', 'number', 'what does', 'mean'],
      reply: "Each score (Stress, Sleep, Insulin, Metabolic, Diet) ranges from 0-10. Higher scores indicate higher risk. You can hover over a body zone or score card to see more details." },
    { patterns: ['simulation', 'simulate', 'run', 'predict'],
      reply: "Head to the Simulations page (⚡ in the sidebar) to run hormone risk predictions. Enter your lifestyle data and the AI will estimate your risk factors." },
    { patterns: ['result', 'interpret', 'understand'],
      reply: "Green means low risk, amber is moderate, and red is high risk. Focus on improving the highest-scoring areas first — small lifestyle changes can make a big difference!" },
    { patterns: ['tip', 'advice', 'recommend', 'improve', 'help'],
      reply: "Here are some general tips:\n• Aim for 7-8 hours of sleep\n• Stay hydrated (2+ litres/day)\n• Manage stress with meditation or walks\n• Regular moderate exercise helps balance hormones" },
    { patterns: ['profile', 'setting', 'update', 'change'],
      reply: "You can update your health profile by clicking 👤 Health Profile in the sidebar. Keeping it up-to-date ensures more accurate predictions." },
    { patterns: ['twin', 'digital', 'body'],
      reply: "Your Digital Twin is a virtual model of your body that reflects your current health metrics. It\u2019s generated from your health profile and simulation data." },
    { patterns: ['food', 'diet', 'eat', 'nutrition'],
      reply: "Check the 🍛 Food Impact page to see how different foods affect your hormonal balance. It helps you make better dietary choices." },
    { patterns: ['history', 'past', 'previous', 'track'],
      reply: "The 🕐 History page stores all your past simulations. You can compare results over time to see if your lifestyle changes are working." },
    { patterns: ['hello', 'hi', 'hey', 'morning', 'afternoon'],
      reply: "Hello! I'm your HormoneLens assistant. How can I help you today?" },
    { patterns: ['thank', 'thanks', 'great', 'awesome'],
      reply: "You're welcome! Let me know if there's anything else I can help with. 😊" },
];

const DEFAULT_REPLY = "I'm not sure about that, but I can help with:\n• Dashboard metrics\n• Running simulations\n• Interpreting results\n• Health & hormone tips\n• Updating your profile\n\nTry asking about any of these!";

function getAssistantReply(userMsg) {
    const lower = userMsg.toLowerCase();
    for (const entry of KNOWLEDGE) {
        if (entry.patterns.some((p) => lower.includes(p))) return entry.reply;
    }
    return DEFAULT_REPLY;
}

/* ─── Component ─── */
export default function ChatWindow({ onClose }) {
    const [messages, setMessages] = useState([
        { role: 'assistant', text: "Hi! I'm your HormoneLens assistant. How can I help you today?" },
    ]);
    const [input, setInput] = useState('');
    const [isTyping, setIsTyping] = useState(false);
    const scrollRef = useRef(null);

    /* Auto-scroll to bottom */
    useEffect(() => {
        if (scrollRef.current) scrollRef.current.scrollTop = scrollRef.current.scrollHeight;
    }, [messages, isTyping]);

    const handleSend = () => {
        const trimmed = input.trim();
        if (!trimmed) return;
        setMessages((ms) => [...ms, { role: 'user', text: trimmed }]);
        setInput('');
        setIsTyping(true);

        /* Simulate assistant thinking delay */
        setTimeout(() => {
            const reply = getAssistantReply(trimmed);
            setMessages((ms) => [...ms, { role: 'assistant', text: reply }]);
            setIsTyping(false);
        }, 600 + Math.random() * 600);
    };

    const handleKey = (e) => { if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); handleSend(); } };

    return (
        <div style={{
            width: 360, height: 480,
            display: 'flex', flexDirection: 'column',
            background: '#fff',
            borderRadius: 20,
            boxShadow: '0 12px 48px rgba(124,58,237,0.18), 0 4px 16px rgba(0,0,0,0.08)',
            border: '1px solid #ede9fe',
            overflow: 'hidden',
            fontFamily: 'system-ui, -apple-system, sans-serif',
        }}>
            {/* Header */}
            <div style={{
                display: 'flex', alignItems: 'center', gap: 10,
                padding: '14px 16px',
                background: 'linear-gradient(135deg, #7c3aed, #6d28d9)',
                color: '#fff',
            }}>
                <div style={{
                    width: 34, height: 34, borderRadius: '50%',
                    background: 'rgba(255,255,255,0.2)',
                    display: 'flex', alignItems: 'center', justifyContent: 'center',
                    fontSize: 16,
                }}>🤖</div>
                <div style={{ flex: 1 }}>
                    <div style={{ fontSize: 14, fontWeight: 700 }}>Luna</div>
                    <div style={{ fontSize: 11, opacity: 0.8 }}>HormoneLens Assistant</div>
                </div>
                <button onClick={onClose} style={{
                    background: 'rgba(255,255,255,0.15)',
                    border: 'none', borderRadius: 8,
                    color: '#fff', width: 30, height: 30,
                    fontSize: 16, cursor: 'pointer',
                    display: 'flex', alignItems: 'center', justifyContent: 'center',
                }}>✕</button>
            </div>

            {/* Messages */}
            <div ref={scrollRef} style={{
                flex: 1, overflowY: 'auto', padding: '14px 14px 8px',
                display: 'flex', flexDirection: 'column', gap: 10,
                background: '#faf9ff',
            }}>
                {messages.map((msg, i) => (
                    <div key={i} style={{
                        alignSelf: msg.role === 'user' ? 'flex-end' : 'flex-start',
                        maxWidth: '82%',
                    }}>
                        <div style={{
                            padding: '10px 14px',
                            borderRadius: msg.role === 'user'
                                ? '14px 14px 4px 14px'
                                : '14px 14px 14px 4px',
                            background: msg.role === 'user'
                                ? 'linear-gradient(135deg, #7c3aed, #6d28d9)'
                                : '#fff',
                            color: msg.role === 'user' ? '#fff' : '#1e1b2e',
                            fontSize: 13.5,
                            lineHeight: 1.55,
                            whiteSpace: 'pre-wrap',
                            boxShadow: msg.role === 'user'
                                ? '0 2px 8px rgba(124,58,237,0.2)'
                                : '0 1px 4px rgba(0,0,0,0.05)',
                            border: msg.role === 'user' ? 'none' : '1px solid #ede9fe',
                        }}>
                            {msg.text}
                        </div>
                    </div>
                ))}
                {isTyping && (
                    <div style={{ alignSelf: 'flex-start', maxWidth: '82%' }}>
                        <div style={{
                            padding: '10px 18px',
                            borderRadius: '14px 14px 14px 4px',
                            background: '#fff',
                            border: '1px solid #ede9fe',
                            display: 'flex', gap: 5, alignItems: 'center',
                        }}>
                            <span style={{ width: 6, height: 6, borderRadius: '50%', background: '#a78bfa', animation: 'chatDot 1.2s ease-in-out infinite' }} />
                            <span style={{ width: 6, height: 6, borderRadius: '50%', background: '#a78bfa', animation: 'chatDot 1.2s ease-in-out 0.2s infinite' }} />
                            <span style={{ width: 6, height: 6, borderRadius: '50%', background: '#a78bfa', animation: 'chatDot 1.2s ease-in-out 0.4s infinite' }} />
                        </div>
                    </div>
                )}
            </div>

            {/* Input */}
            <div style={{
                display: 'flex', gap: 8,
                padding: '10px 12px',
                borderTop: '1px solid #ede9fe',
                background: '#fff',
            }}>
                <input
                    type="text"
                    placeholder="Ask me anything…"
                    value={input}
                    onChange={(e) => setInput(e.target.value)}
                    onKeyDown={handleKey}
                    style={{
                        flex: 1, padding: '10px 14px',
                        borderRadius: 12, border: '1.5px solid #e2ddf5',
                        fontSize: 13.5, outline: 'none',
                        fontFamily: 'inherit',
                        background: '#faf9ff',
                    }}
                />
                <button onClick={handleSend} style={{
                    width: 40, height: 40, borderRadius: 12,
                    border: 'none',
                    background: 'linear-gradient(135deg, #7c3aed, #6d28d9)',
                    color: '#fff', fontSize: 16,
                    cursor: 'pointer',
                    display: 'flex', alignItems: 'center', justifyContent: 'center',
                    boxShadow: '0 2px 8px rgba(124,58,237,0.25)',
                    flexShrink: 0,
                }}>
                    ↑
                </button>
            </div>

            <style>{`
                @keyframes chatDot {
                    0%,60%,100% { opacity: 0.3; transform: translateY(0); }
                    30% { opacity: 1; transform: translateY(-4px); }
                }
            `}</style>
        </div>
    );
}
