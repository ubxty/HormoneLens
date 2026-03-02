import React from 'react';
import { createRoot } from 'react-dom/client';
import InteractiveDigitalTwin from './components/InteractiveDigitalTwin';

const el = document.getElementById('twin-root');
if (el) {
    createRoot(el).render(<InteractiveDigitalTwin />);
}
