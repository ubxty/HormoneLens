import React, { useState } from 'react';
import SelectionCard from './SelectionCard';

/* ─── step definitions with card-friendly, human wording ─── */
const STEPS = [
    {
        key: 'gender',
        label: 'Tell us about yourself',
        type: 'cards',
        options: [
            { value: 'male',   emoji: '👨', label: 'Male',   description: 'I identify as male' },
            { value: 'female', emoji: '👩', label: 'Female', description: 'I identify as female' },
            { value: 'other',  emoji: '🧑', label: 'Other',  description: 'I prefer another option' },
        ],
        validate: (v) => (v ? null : 'Please select an option.'),
    },
    {
        key: 'weight',
        label: "What's your weight?",
        type: 'number',
        placeholder: 'e.g. 65',
        unit: 'kg',
        min: 30, max: 250,
        maxDigits: 3,
        validate: (v) => {
            const n = Number(v);
            if (!v || isNaN(n)) return 'Please enter your weight.';
            if (n < 30 || n > 250) return 'Please enter a realistic weight (30–250 kg).';
            return null;
        },
    },
    {
        key: 'height',
        label: 'How tall are you?',
        type: 'height_ft_in',
        validate: (v) => {
            if (!v || typeof v !== 'object') return 'Please enter your height.';
            if (v.feet === '' || v.feet == null) return 'Please enter feet.';
            if (v.inches === '' || v.inches == null) return 'Please enter inches.';
            const ft = Number(v.feet);
            const inc = Number(v.inches);
            if (isNaN(ft) || ft < 3 || ft > 8) return 'Feet must be between 3 and 8.';
            if (isNaN(inc) || inc < 0 || inc > 11) return 'Inches must be between 0 and 11.';
            return null;
        },
    },
    {
        key: 'avg_sleep_hours',
        label: "How's your sleep been?",
        type: 'cards',
        options: [
            { value: '4',  emoji: '😫', label: 'Less than 5 hours',  description: 'I barely get enough rest' },
            { value: '6',  emoji: '😐', label: 'About 5–6 hours',    description: 'I could use more sleep' },
            { value: '7',  emoji: '😊', label: 'Around 7–8 hours',   description: 'I sleep pretty well' },
            { value: '9',  emoji: '😴', label: 'More than 8 hours',  description: 'I love my sleep!' },
        ],
        validate: (v) => (v ? null : 'Please select your sleep pattern.'),
    },
    {
        key: 'stress_level',
        label: 'How stressed do you feel?',
        type: 'cards',
        options: [
            { value: 'high',   emoji: '😵', label: 'I feel stressed most of the time',  description: 'Work, life — it all adds up' },
            { value: 'medium', emoji: '🙂', label: 'I usually keep my calm',             description: 'Some days are tough, but I manage' },
            { value: 'low',    emoji: '😌', label: 'I only get stressed sometimes',       description: "I'm pretty relaxed overall" },
        ],
        validate: (v) => (v ? null : 'Please tell us about your stress level.'),
    },
    {
        key: 'physical_activity',
        label: 'How active is your lifestyle?',
        type: 'cards',
        options: [
            { value: 'sedentary', emoji: '🛋️', label: 'Mostly sitting',       description: 'Desk job, not much movement' },
            { value: 'moderate',  emoji: '🚶', label: 'Somewhat active',       description: 'I walk or exercise a few times a week' },
            { value: 'active',    emoji: '🏃', label: 'Very active & fit',     description: 'I work out regularly and stay on my feet' },
        ],
        validate: (v) => (v ? null : 'Please select your activity level.'),
    },
    {
        key: 'water_intake',
        label: 'How much water do you drink daily?',
        type: 'cards',
        options: [
            { value: '1', emoji: '🥤', label: 'Less than 1 litre',  description: 'I forget to drink water often' },
            { value: '2', emoji: '💧', label: 'About 1–2 litres',   description: 'I try to stay hydrated' },
            { value: '3', emoji: '🚰', label: 'About 2–3 litres',   description: 'I drink plenty of water' },
            { value: '4', emoji: '🌊', label: 'More than 3 litres', description: 'Hydration is my priority!' },
        ],
        validate: (v) => (v ? null : 'Please select your water intake.'),
    },
    {
        key: 'disease_type',
        label: 'Do you have any health conditions?',
        type: 'health_condition',
        validate: (v) => {
            if (!v || typeof v !== 'object' || v.hasCondition == null) return 'Please select an option.';
            if (v.hasCondition) {
                if (!v.condition) return 'Please select your condition from the list.';
                if (v.condition === 'Other' && (!v.otherText || !v.otherText.trim())) return 'Please specify your condition.';
                if (v.otherText && v.otherText.length > 100) return 'Maximum 100 characters.';
            }
            return null;
        },
    },
    {
        key: 'eating_habits',
        label: 'Tell us about your eating habits',
        type: 'textarea',
        placeholder: 'e.g. I usually skip breakfast, love snacking late at night...',
        optional: true,
        validate: (v) => {
            if (v && v.length > 1000) return 'Maximum 1000 characters.';
            return null;
        },
    },
];

export { STEPS };

/* ─── shared input styles ─── */
const baseInput = {
    width: '100%',
    padding: '14px 18px',
    fontSize: 15,
    borderRadius: 14,
    border: '2px solid #e2ddf5',
    outline: 'none',
    transition: 'border-color 0.2s, box-shadow 0.2s, background 0.2s',
    fontFamily: 'system-ui, -apple-system, sans-serif',
    background: 'rgba(255,255,255,0.9)',
    boxSizing: 'border-box',
    color: '#1e1b2e',
};
const focusInput = {
    borderColor: '#7c3aed',
    boxShadow: '0 0 0 4px rgba(124,58,237,0.08)',
    background: '#fff',
};

export default function HealthQuestionForm({ stepIndex, value, onChange, error }) {
    const step = STEPS[stepIndex];
    const [focused, setFocused] = useState(false);

    if (!step) return null;

    const inputStyle = { ...baseInput, ...(focused ? focusInput : {}) };

    const renderInput = () => {
        /* Card-based selection */
        if (step.type === 'cards') {
            return (
                <div style={{ display: 'flex', flexDirection: 'column', gap: 10 }}>
                    {step.options.map((opt) => (
                        <SelectionCard
                            key={opt.value}
                            emoji={opt.emoji}
                            label={opt.label}
                            description={opt.description}
                            selected={value === opt.value}
                            onClick={() => onChange(opt.value)}
                        />
                    ))}
                </div>
            );
        }

        /* Height: Feet + Inches */
        if (step.type === 'height_ft_in') {
            const val = (value && typeof value === 'object') ? value : { feet: '', inches: '' };
            return (
                <div style={{ display: 'flex', gap: 16 }}>
                    <div style={{ flex: 1 }}>
                        <label style={{ display: 'block', fontSize: 13, fontWeight: 600, color: '#6d28d9', marginBottom: 6 }}>
                            Feet
                        </label>
                        <input
                            type="number"
                            min={3}
                            max={8}
                            placeholder="5"
                            value={val.feet}
                            onChange={(e) => onChange({ ...val, feet: e.target.value })}
                            onFocus={() => setFocused(true)}
                            onBlur={() => setFocused(false)}
                            style={inputStyle}
                        />
                    </div>
                    <div style={{ flex: 1 }}>
                        <label style={{ display: 'block', fontSize: 13, fontWeight: 600, color: '#6d28d9', marginBottom: 6 }}>
                            Inches
                        </label>
                        <input
                            type="number"
                            min={0}
                            max={11}
                            placeholder="7"
                            value={val.inches}
                            onChange={(e) => onChange({ ...val, inches: e.target.value })}
                            onFocus={() => setFocused(true)}
                            onBlur={() => setFocused(false)}
                            style={inputStyle}
                        />
                    </div>
                </div>
            );
        }

        /* Health condition cards + smart dropdown */
        if (step.type === 'health_condition') {
            const val = (value && typeof value === 'object') ? value : {};
            const CONDITIONS = [
                'PCOS / PCOD',
                'Diabetes',
                'Thyroid Disorder',
                'Insulin Resistance',
                'Other',
            ];
            const selectStyle = {
                ...inputStyle,
                appearance: 'none',
                WebkitAppearance: 'none',
                paddingRight: 44,
                cursor: 'pointer',
            };
            return (
                <div style={{ display: 'flex', flexDirection: 'column', gap: 10 }}>
                    <SelectionCard
                        emoji="🩺"
                        label="I have a condition"
                        description="I have a diagnosed health condition"
                        selected={val.hasCondition === true}
                        onClick={() => onChange({ ...val, hasCondition: true })}
                    />
                    <SelectionCard
                        emoji="🌿"
                        label="I am generally healthy"
                        description="No major health conditions"
                        selected={val.hasCondition === false}
                        onClick={() => onChange({ hasCondition: false })}
                    />

                    {val.hasCondition === true && (
                        <div style={{
                            marginTop: 4,
                            animation: 'conditionSlideIn 0.28s cubic-bezier(.4,0,.2,1)',
                            display: 'flex', flexDirection: 'column', gap: 12,
                        }}>
                            {/* Dropdown */}
                            <div>
                                <label style={{ display: 'block', fontSize: 13, fontWeight: 600, color: '#6d28d9', marginBottom: 6 }}>
                                    Select your condition
                                </label>
                                <div style={{ position: 'relative' }}>
                                    <select
                                        value={val.condition || ''}
                                        onChange={(e) => onChange({ ...val, condition: e.target.value, otherText: val.otherText || '' })}
                                        onFocus={() => setFocused(true)}
                                        onBlur={() => setFocused(false)}
                                        style={{
                                            ...selectStyle,
                                            ...(focused ? focusInput : {}),
                                        }}
                                    >
                                        <option value="" disabled>Select a condition…</option>
                                        {CONDITIONS.map((c) => (
                                            <option key={c} value={c}>{c}</option>
                                        ))}
                                    </select>
                                    <span style={{
                                        position: 'absolute', right: 16, top: '50%',
                                        transform: 'translateY(-50%)',
                                        fontSize: 12, color: '#7c3aed',
                                        pointerEvents: 'none', fontWeight: 700,
                                    }}>▼</span>
                                </div>
                            </div>

                            {/* "Other" free-text */}
                            {val.condition === 'Other' && (
                                <div style={{ animation: 'conditionSlideIn 0.22s cubic-bezier(.4,0,.2,1)' }}>
                                    <label style={{ display: 'block', fontSize: 13, fontWeight: 600, color: '#6d28d9', marginBottom: 6 }}>
                                        Please specify your condition
                                    </label>
                                    <input
                                        type="text"
                                        placeholder="Type your condition here…"
                                        maxLength={100}
                                        value={val.otherText || ''}
                                        onChange={(e) => onChange({ ...val, otherText: e.target.value })}
                                        onFocus={() => setFocused(true)}
                                        onBlur={() => setFocused(false)}
                                        style={{ ...inputStyle, ...(focused ? focusInput : {}) }}
                                    />
                                </div>
                            )}
                        </div>
                    )}

                    <style>{`
                        @keyframes conditionSlideIn {
                            from { opacity: 0; transform: translateY(-8px); }
                            to   { opacity: 1; transform: translateY(0); }
                        }
                    `}</style>
                </div>
            );
        }

        if (step.type === 'textarea') {
            return (
                <textarea
                    rows={3}
                    placeholder={step.placeholder}
                    value={value || ''}
                    onChange={(e) => onChange(e.target.value)}
                    onFocus={() => setFocused(true)}
                    onBlur={() => setFocused(false)}
                    style={{ ...inputStyle, resize: 'vertical', minHeight: 80 }}
                />
            );
        }

        /* Number or text input with optional unit */
        return (
            <div style={{ position: 'relative' }}>
                <input
                    type={step.type}
                    placeholder={step.placeholder}
                    min={step.min}
                    max={step.max}
                    step={step.step}
                    value={value ?? ''}
                    onChange={(e) => {
                        let v = e.target.value;
                        if (step.maxDigits) {
                            const digits = v.replace(/[^\d]/g, '');
                            if (digits.length > step.maxDigits) return;
                        }
                        onChange(v);
                    }}
                    onFocus={() => setFocused(true)}
                    onBlur={() => setFocused(false)}
                    style={{ ...inputStyle, paddingRight: step.unit ? 52 : 18 }}
                />
                {step.unit && (
                    <span style={{
                        position: 'absolute', right: 16, top: '50%',
                        transform: 'translateY(-50%)',
                        fontSize: 13, fontWeight: 600, color: '#a78bfa',
                        pointerEvents: 'none',
                    }}>
                        {step.unit}
                    </span>
                )}
            </div>
        );
    };

    return (
        <div style={{ width: '100%' }}>
            <label style={{
                display: 'block', marginBottom: 14,
                fontSize: 16, fontWeight: 700, color: '#4c1d95',
                fontFamily: 'system-ui, -apple-system, sans-serif',
            }}>
                {step.label}
                {step.optional && (
                    <span style={{ fontWeight: 400, color: '#a78bfa', marginLeft: 8, fontSize: 13 }}>
                        (optional)
                    </span>
                )}
            </label>

            {renderInput()}

            {error && (
                <p style={{
                    marginTop: 10, fontSize: 13, color: '#ef4444',
                    fontFamily: 'system-ui, -apple-system, sans-serif',
                    display: 'flex', alignItems: 'center', gap: 6,
                }}>
                    <span style={{ fontSize: 16 }}>⚠️</span> {error}
                </p>
            )}
        </div>
    );
}
