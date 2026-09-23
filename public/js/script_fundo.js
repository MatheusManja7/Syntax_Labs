// JS - Efeito Fundo — agora cobre o site inteiro (fixed)
let vantaEffect = VANTA.TOPOLOGY({
    el: "#background",
    mouseControls: true,
    touchControls: true,
    gyroControls: false,
    minHeight: 200.00,
    minWidth: 200.00,
    scale: 1.00,
    scaleMobile: 1.00,
    color: 0x5f9e00,        
    backgroundColor: 0x141414, 
    spacing: 18.00,
    maxDistance: 22.00
});

// Varia o efeito de fundo 
function variarEfeitoFundo() {
    if (!vantaEffect) return;

    const novoSpacing = 14 + Math.random() * 12; 
    const novoMaxDistance = 15 + Math.random() * 15;

    vantaEffect.setOptions({
        spacing: novoSpacing,
        maxDistance: novoMaxDistance
    });
}

setInterval(variarEfeitoFundo, 9000); // muda a cada 9s