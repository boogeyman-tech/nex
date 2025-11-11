import React from 'react';
import ReactDOM from 'react-dom/client';
import DarkVeil from './components/DarkVeil';
import './styles/dashboard.css';

const container = document.getElementById('react-dark-veil');

if (container) {
  const root = ReactDOM.createRoot(container);
  root.render(
    <React.StrictMode>
      <DarkVeil />
    </React.StrictMode>
  );
}
