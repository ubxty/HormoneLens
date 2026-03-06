import React, { useState } from 'react';

/* ─── step definitions ─── */
const STEPS = [
    {
        key: 'gender',
        label: 'What is your gender?',
        type: 'select',
        options: [
            { value: 'female', label: 'Female' },
            { value: 'male', label: 'Male' },
        ],
        validate: (v) => (v ? null : 'Please select your gender.'),
    },
    {
        key: 'weight',
        label: 'What is your weight (kg)?',
        type: 'number',
        placeholder: 'e.g. 65',
        min: 20, max: 300,
        validate: (v) => {
            const n = Number(v);
            if (!v || isNaN(n)) return 'Please enter your weight.';
            if (n < 20 || n > 300) return 'Weight must be between 20 and 300 kg.';
            return null;
        },
    },
    {
        key: 'height',
        label: 'What is your height (cm)?',
        type: 'number',
        placeholder: 'e.g. 165',
        min: 50, max: 250,
        validate: (v) => {
            const n = Number(v);
            if (!v || isNaN(n)) return 'Please enter your height.';
            if (n < 50 || n > 250) return 'Height must be between 50 and 250 cm.';
            return null;
        },
    },
    {
        key: 'avg_sleep_hours',
        label: 'How many hours do you sleep on average?',
        type: 'number',
        placeholder: 'e.g. 7',
        min: 0, max: 24, step: 0.5,
        validate: (v) => {
            const n = Number(v);
            if (v === '' || isNaN(n)) return 'Please enter your average sleep hours.';
            if (n < 0 || n > 24) return 'Must be between 0 and 24 hours.';
            return null;
        },
    },
    {
        key: 'stress_level',
        label: 'How would you describe your stress level?',
        type: 'select',
        options: [
            { value: 'low', label: 'Low' },
            { value: 'medium', label: 'Medium' },
            { value: 'high', label: 'High' },
        ],
        validate: (v) => (v ? null : 'Please select your stress level.'),
    },
    {
        key: 'physical_activity',
        label: 'How active are you physically?',
        type: 'select',
        options: [
            { value: 'sedentary', label: 'Sedentary' },
            { value: 'moderate', label: 'Moderate' },
            { value: 'active', label: 'Active' },
        ],
        validate: (v) => (v ? null : 'Please select your activity level.'),
    },
    {
        key: 'water_intake',
        label: 'How many litres of water do you drink daily?',
        type: 'number',
        placeholder: 'e.g. 2',
        min: 0, max: 20, step: 0.5,
        validate: (v) => {
            const n = Number(v);
            if (v === '' || isNaN(n)) return 'Please enter your daily water intake.';
            if (n < 0 || n > 20) return 'Must be between 0 and 20 litres.';
            return null;
        },
    },
    {
        key: 'disease_type',
        label: 'Any known conditions or diseases?',
        type: 'text',
        placeholder: 'e.g. PCOS, Hypothyroid, or None',
        validate: (v) => {
            if (!v || !v.trim()) return 'Please enter a condition or type "None".';
            if (v.length > 100) return 'Maximum 100 characters.';
            return null;
        },
    },
    {
        key: 'eating_habits',
        label: 'Describe your eating habits (optional)',
        type: 'textarea',
        placeholder: 'e.g. I usually skip breakfast...',
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
    padding: '12px 16px',
    fontSize: 15,
    borderRadius: 12,
    border: '1.5px solid #e2ddf5',
    outline: 'none',
    transition: 'border-color 0.2s, box-shadow 0.2s',
    fontFamily: 'system-ui, -apple-system, sans-serif',
    background: '#faf9ff',
    boxSizing: 'border-box',
};
const focusInput = {
    borderColor: '#7c3aed',
    boxShadow: '0 0 0 3px rgba(124,58,237,0.08)',
};

export default function HealthQuestionForm({ stepIndex, value, onChange, error }) {
    const step = STEPS[stepIndex];
    const [focused, setFocused] = useState(false);

    if (!step) return null;

    const inputStyle = { ...baseInput, ...(focused ? focusInput : {}) };

    const renderInput = () => {
        if (step.type === 'select') {
            return (
                <select
                    value={value || ''}
                    onChange={(e) => onChange(e.target.value)}
                    onFocus={() => setFocused(true)}
                    onBlur={() => setFocused(false)}
                    style={{ ...inputStyle, cursor: 'pointer', appearance: 'none' }}
                >
                    <option value="">— Select —</option>
                    {step.options.map((o) => (
                        <option key={o.value} value={o.value}>{o.label}</option>
                    ))}
                </select>
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
                    style={{ ...inputStyle, resize: 'vertical' }}
                />
            );
        }
        return (
            <input
                type={step.type}
                placeholder={step.placeholder}
                min={step.min}
                max={step.max}
                step={step.step}
                value={value ?? ''}
                onChange={(e) => onChange(e.target.value)}
                onFocus={() => setFocused(true)}
                onBlur={() => setFocused(false)}
                style={inputStyle}
            />
        );
    };

    return (
        <div style={{ width: '100%', maxWidth: 380 }}>
            <label style={{
                display: 'block', marginBottom: 10,
                fontSize: 14, fontWeight: 600, color: '#4c1d95',
                fontFamily: 'system-ui, -apple-system, sans-serif',
            }}>
                {step.label}
                {step.optional && <span style={{ fontWeight: 400, color: '#a78bfa', marginLeft: 6 }}>(optional)</span>}
            </label>

            {renderInput()}

            {error && (
                <p style={{
                    marginTop: 8, fontSize: 13, color: '#ef4444',
                    fontFamily: 'system-ui, -apple-system, sans-serif',
                }}>{error}</p>
            )}
        </div>
    );
}
