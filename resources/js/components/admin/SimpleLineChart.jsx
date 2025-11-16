import React from 'react';

export default function SimpleLineChart({ data = [], width = '100%', height = 200, color = '#10B981' }) {
	const padding = 24;
	const w = 600;
	const h = height;
	const max = Math.max(1, ...data);
	const step = (w - padding * 2) / Math.max(1, data.length - 1);

	const points = data.map((v, i) => {
		const x = padding + i * step;
		const y = h - padding - (v / max) * (h - padding * 2);
		return `${x},${y}`;
	}).join(' ');

	return (
		<div style={{ width: width }}>
			<svg viewBox={`0 0 ${w} ${h}`} width="100%" height={h}>
				{/* baseline */}
				<line x1={padding} x2={w - padding} y1={h - padding} y2={h - padding} stroke="#E5E7EB" />
				{/* area fill */}
				<polyline points={`${points} ${w - padding},${h - padding} ${padding},${h - padding}`} fill={color} opacity="0.12" />
				{/* line */}
				<polyline points={points} fill="none" stroke={color} strokeWidth="3" strokeLinejoin="round" strokeLinecap="round" />
			</svg>
		</div>
	);
}


