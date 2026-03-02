"use client";

import { useState, useEffect } from "react";
import { AnimatePresence, motion } from "framer-motion";

const SENTENCES = [
  "Your PCOS Symptoms Worsen....",
  "Insulin Resistance Begins....",
  "Metabolic Damage Happens....",
  "Hormonal Imbalance Starts....",
];

const CHAR_DELAY  = 40;   // ms per character
const PAUSE_MS    = 2000; // ms pause at full sentence
const FADE_SECS   = 0.6;  // fade duration in seconds
const FADE_MS     = FADE_SECS * 1000;

export default function HeroTyping() {
  const [index,         setIndex]         = useState(0);
  const [displayedText, setDisplayedText] = useState("");
  const [charIndex,     setCharIndex]     = useState(0);
  const [isFadingOut,   setIsFadingOut]   = useState(false);

  useEffect(() => {
    const sentence = SENTENCES[index];

    // ── Phase: fading out ─────────────────────────────────────────────
    // Framer Motion is animating opacity → 0 via the `animate` prop.
    // Wait for the animation to finish, then advance to the next sentence.
    if (isFadingOut) {
      const t = setTimeout(() => {
        setIndex((i) => (i + 1) % SENTENCES.length);
        setDisplayedText("");
        setCharIndex(0);
        setIsFadingOut(false); // triggers re-render with new index → fade-in
      }, FADE_MS + 60);
      return () => clearTimeout(t);
    }

    // ── Phase: typing ────────────────────────────────────────────────
    if (charIndex < sentence.length) {
      const t = setTimeout(() => {
        setCharIndex((c) => c + 1);
        setDisplayedText(sentence.slice(0, charIndex + 1));
      }, CHAR_DELAY);
      return () => clearTimeout(t);
    }

    // ── Phase: pausing (sentence fully typed) ────────────────────────
    if (charIndex === sentence.length && sentence.length > 0) {
      const t = setTimeout(() => setIsFadingOut(true), PAUSE_MS);
      return () => clearTimeout(t);
    }
  }, [index, charIndex, isFadingOut]);

  return (
    <div className="flex flex-col items-center text-center px-4 py-16 select-none">

      {/* ── LINE 1 — Static gradient ────────────────────────────────── */}
      <h1 className="text-5xl md:text-6xl font-bold leading-tight tracking-tight
                     bg-gradient-to-r from-pink-500 to-purple-600
                     bg-clip-text text-transparent">
        Stay Ahead Before
      </h1>

      {/* ── LINE 2 — Fade + type loop ───────────────────────────────── */}
      {/*
        min-h prevents layout shift while sentences change length.
        key={index} forces Framer to treat each sentence as a new
        element — enter animation (initial → animate) fires on mount.
        isFadingOut drives the animate prop to opacity 0 BEFORE the
        index changes, so the fade-out visually completes first.
      */}
      <div className="mt-3 min-h-[3.5rem] md:min-h-[4rem] flex items-center justify-center">
        <AnimatePresence mode="wait">
          <motion.h2
            key={index}
            initial={{ opacity: 0, y: 10 }}
            animate={{
              opacity: isFadingOut ? 0 : 1,
              y:       isFadingOut ? -10 : 0,
            }}
            exit={{ opacity: 0, y: -10 }}
            transition={{ duration: FADE_SECS, ease: [0.4, 0, 0.2, 1] }}
            className="text-4xl md:text-5xl font-semibold leading-tight
                       tracking-tight text-gray-700 dark:text-gray-200
                       flex items-baseline"
          >
            {displayedText}
            {/* Softly blinking cursor */}
            <span
              aria-hidden="true"
              className="inline-block w-[3px] ml-[3px] self-stretch rounded-full
                         bg-gradient-to-b from-pink-500 to-purple-600
                         animate-cursor-blink"
            />
          </motion.h2>
        </AnimatePresence>
      </div>

    </div>
  );
}
