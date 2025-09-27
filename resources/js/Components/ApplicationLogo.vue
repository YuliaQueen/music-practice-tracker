<template>
    <svg viewBox="0 0 160 120" xmlns="http://www.w3.org/2000/svg">
        <!-- Градиент для яркости -->
        <defs>
            <linearGradient id="noteGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" style="stop-color:#3B82F6;stop-opacity:1" />
                <stop offset="50%" style="stop-color:#8B5CF6;stop-opacity:1" />
                <stop offset="100%" style="stop-color:#EC4899;stop-opacity:1" />
            </linearGradient>
            <linearGradient id="staffGradient" x1="0%" y1="0%" x2="100%" y2="0%">
                <stop offset="0%" style="stop-color:#3B82F6;stop-opacity:0.8" />
                <stop offset="100%" style="stop-color:#8B5CF6;stop-opacity:0.8" />
            </linearGradient>
        </defs>
        
        <!-- Музыкальные линии (нотный стан) -->
        <g stroke="url(#staffGradient)" stroke-width="2" opacity="0.7">
            <line x1="15" y1="100" x2="145" y2="100"/>
            <line x1="15" y1="105" x2="145" y2="105"/>
            <line x1="15" y1="110" x2="145" y2="110"/>
            <line x1="15" y1="115" x2="145" y2="115"/>
        </g>
        
        <!-- Основная музыкальная нота -->
        <g fill="url(#noteGradient)">
            <!-- Основание ноты (овал) -->
            <ellipse cx="30" cy="85" rx="12" ry="18"/>
            <!-- Стержень ноты -->
            <rect x="41" y="20" width="4" height="65"/>
            <!-- Флажок ноты (более выразительный) -->
            <path d="M45 20 Q55 10 65 20 Q55 30 45 20 Z"/>
            <!-- Дополнительный флажок для выразительности -->
            <path d="M45 20 Q50 15 55 20 Q50 25 45 20 Z"/>
        </g>
        
        <!-- Дополнительные музыкальные элементы -->
        <g fill="url(#noteGradient)" opacity="0.6">
            <!-- Мелкие ноты вокруг -->
            <ellipse cx="70" cy="60" rx="6" ry="9"/>
            <rect x="75" y="40" width="2" height="20"/>
            <path d="M77 40 Q82 35 87 40 Q82 45 77 40 Z"/>
            
            <ellipse cx="95" cy="45" rx="5" ry="7"/>
            <rect x="99" y="30" width="2" height="15"/>
            <path d="M101 30 Q105 27 109 30 Q105 33 101 30 Z"/>
            
            <ellipse cx="115" cy="55" rx="4" ry="6"/>
            <rect x="118" y="40" width="2" height="15"/>
            <path d="M120 40 Q124 37 128 40 Q124 43 120 40 Z"/>
            
            <ellipse cx="130" cy="40" rx="3" ry="5"/>
            <rect x="132" y="25" width="2" height="15"/>
            <path d="M134 25 Q137 22 140 25 Q137 28 134 25 Z"/>
        </g>
        
        <!-- Музыкальные символы -->
        <g fill="url(#noteGradient)" opacity="0.4">
            <!-- Скрипичный ключ (стилизованный) -->
            <path d="M15 30 Q20 25 25 30 Q20 35 15 30 Q15 40 20 45 Q25 50 20 55 Q15 50 15 60 Q20 65 25 60"/>
            <!-- Диез -->
            <rect x="120" y="20" width="2" height="15"/>
            <rect x="118" y="22" width="6" height="2"/>
            <rect x="118" y="28" width="6" height="2"/>
            <rect x="118" y="34" width="6" height="2"/>
            <!-- Бемоль -->
            <rect x="140" y="25" width="2" height="12"/>
            <path d="M140 25 Q135 20 130 25 Q135 30 140 25"/>
            <path d="M140 30 Q135 25 130 30 Q135 35 140 30"/>
            <!-- Бекар -->
            <rect x="150" y="20" width="2" height="15"/>
            <rect x="148" y="22" width="6" height="2"/>
            <rect x="148" y="28" width="6" height="2"/>
            <rect x="148" y="34" width="6" height="2"/>
        </g>
        
        <!-- Блестящие эффекты -->
        <g fill="white" opacity="0.8">
            <circle cx="35" cy="80" r="2"/>
            <circle cx="50" cy="25" r="1.5"/>
            <circle cx="75" cy="55" r="1"/>
            <circle cx="95" cy="40" r="1.2"/>
            <circle cx="115" cy="50" r="0.8"/>
            <circle cx="130" cy="35" r="0.6"/>
            <circle cx="145" cy="45" r="0.5"/>
        </g>
    </svg>
</template>
