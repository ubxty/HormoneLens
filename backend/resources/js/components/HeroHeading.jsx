import { useState, useEffect } from "react";
import { AnimatePresence, motion } from "framer-motion";

const SENTENCES = [
  "Before You Live It",
  "Before Insulin Resistance Begins",
  "Before PCOS Symptoms Worsen",
  "Before Metabolic Damage Happens",
];

const TYPING_SPEED  = 52;   // ms per character
const PAUSE_MS      = 2000; // pause at full sentence
const FADE_DURATION = 0.55; // seconds — must match Framer variants

export default function HeroHeading() {
  const [index,     setIndex]     = useState(0);
  const [displayed, setDisplayed] = useState("");
  // phases: "typing" | "pausing" | "fading"
  const [phase,     setPhase]     = useState("typing");

  // ------------------------------------------------------------
  // Phase machine — every branch returns its OWN cleanup so
  // React can cancel the timer before the next render fires.
  // Without this, timers stack up and the loop jams.
  // ------------------------------------------------------------
  useEffect(() => {
    const sentence = SENTENCES[index];

    if (phase === "typing") {
      if (displayed.length < sentence.length) {
        const t = setTimeout(
          () => setDisplayed(sentence.slice(0, displayed.length + 1)),
          TYPING_SPEED,
        );
        return () => clearTimeout(t);
      }
      // Fully typed → move to pause
      const t = setTimeout(() => setPhase("pausing"), 50);
      return () => clearTimeout(t);
    }

    if (phase === "pausing") {
      const t = setTimeout(() => setPhase("fading"), PAUSE_MS);
      return () => clearTimeout(t);
    }

    if (phase === "fading") {
      // Wait for Framer exit animation, then advance
      const t = setTimeout(() => {
        setIndex((i) => (i + 1) % SENTENCES.length);
        setDisplayed("");
        setPhase("typing");
      }, FADE_DURATION * 1000 + 80);
      return () => clearTimeout(t);
    }
  }, [phase, displayed, index]);

  // Framer Motion fade variants
  const fadeVariants = {
    enter: { opacity: 0, y: 6 },
    visible: {
      opacity: 1,
      y: 0,
      transition: { duration: FADE_DURATION, ease: [0.4, 0, 0.2, 1] },
    },
    exit: {
      opacity: 0,
      y: -6,
      transition: { duration: FADE_DURATION, ease: [0.4, 0, 0.2, 1] },
    },
  };

  const isFading = phase === "fading";

  return (
    <div className="flex flex-col items-center text-center px-4 py-16 select-none">
      {/* ── LINE 1 — Static gradient ───────────────────────────────────── */}
      <h1
        className="text-5xl md:text-6xl font-bold leading-tight tracking-tight
                   bg-gradient-to-r from-pink-500 to-purple-600
                   bg-clip-text text-transparent"
      >
        See Your Health
      </h1>

      {/* ── LINE 2 — Fade + Type loop ──────────────────────────────────── */}
      {/*
          min-h prevents layout shift as sentence length varies.
          AnimatePresence tracks the sentence key so Framer knows
          when to fire exit → enter transitions.
      */}
      <div className="mt-3 min-h-[3.5rem] md:min-h-[4rem] flex items-center justify-center">
        <AnimatePresence mode="wait">
          {!isFading && (
            <motion.h2
              key={index}
              variants={fadeVariants}
              initial="enter"
              animate="visible"
              exit="exit"
              className="text-4xl md:text-5xl font-semibold leading-tight
                         tracking-tight text-gray-700 dark:text-gray-200
                         flex items-baseline"
            >
              {displayed}

              {/* Softly blinking cursor — only visible while typing/pausing */}
              <span
                aria-hidden="true"
                className="inline-block w-[3px] ml-[3px] self-stretch rounded-full
                           bg-gradient-to-b from-pink-500 to-purple-600
                           animate-cursor-blink"
              />
            </motion.h2>
          )}
        </AnimatePresence>
      </div>
    </div>
  );
}
