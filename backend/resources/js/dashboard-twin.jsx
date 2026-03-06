import React from 'react';
import { createRoot } from 'react-dom/client';
import DigitalTwin3D from './components/DigitalTwin3D';
import SimulationCharacter from './components/SimulationCharacter';
import DashboardTour from './components/tour/DashboardTour';

class ErrorBoundary extends React.Component {
    constructor(props) { super(props); this.state = { error: null }; }
    static getDerivedStateFromError(error) { return { error }; }
    componentDidCatch(e, info) { console.error('Dashboard crash:', e, info); }
    render() {
        if (this.state.error) return (
            <div style={{ padding: 40, color: '#ef4444', fontFamily: 'monospace', fontSize: 14 }}>
                <h2 style={{ margin: '0 0 12px' }}>Dashboard Error</h2>
                <pre style={{ whiteSpace: 'pre-wrap', background: '#1e1e1e', color: '#f87171', padding: 16, borderRadius: 8 }}>{this.state.error.toString()}{'\n'}{this.state.error.stack}</pre>
            </div>
        );
        return this.props.children;
    }
}

const el = document.getElementById('twin-root');
if (el) {
    createRoot(el).render(
        <ErrorBoundary>
            <DigitalTwin3D />
        </ErrorBoundary>
    );
}

const simEl = document.getElementById('simulation-character-root');
if (simEl) {
    createRoot(simEl).render(
        <ErrorBoundary>
            <SimulationCharacter />
        </ErrorBoundary>
    );
}

const tourEl = document.getElementById('dashboard-tour-root');
if (tourEl) {
    const userId = tourEl.dataset.userId || '';
    createRoot(tourEl).render(
        <ErrorBoundary>
            <DashboardTour userId={userId} />
        </ErrorBoundary>
    );
}
