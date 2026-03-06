import React from 'react';
import { createRoot } from 'react-dom/client';
import OnboardingPage from './components/onboarding/OnboardingPage';

class ErrorBoundary extends React.Component {
    constructor(props) { super(props); this.state = { error: null }; }
    static getDerivedStateFromError(error) { return { error }; }
    componentDidCatch(e, info) { console.error('Onboarding crash:', e, info); }
    render() {
        if (this.state.error) return (
            <div style={{ padding: 40, color: '#ef4444', fontFamily: 'monospace', fontSize: 14 }}>
                <h2 style={{ margin: '0 0 12px' }}>Onboarding Error</h2>
                <pre style={{ whiteSpace: 'pre-wrap', background: '#1e1e1e', color: '#f87171', padding: 16, borderRadius: 8 }}>
                    {this.state.error.toString()}{'\n'}{this.state.error.stack}
                </pre>
            </div>
        );
        return this.props.children;
    }
}

const el = document.getElementById('onboarding-root');
if (el) {
    createRoot(el).render(
        <ErrorBoundary>
            <OnboardingPage />
        </ErrorBoundary>
    );
}
