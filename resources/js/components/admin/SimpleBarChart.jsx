import React from 'react';

export default function SimpleBarChart({ data = [], width = '100%', height = 200, color = '#6366F1' }) {
	const padding = 24;
	const w = 600;
	const h = height;
	const max = Math.max(1, ...data);
	const barWidth = (w - padding * 2) / data.length;

	return (
		<div style={{ width: width }}>
			<svg viewBox={`0 0 ${w} ${h}`} width="100%" height={h}>
				<defs>
					<linearGradient id="barGradient" x1="0" x2="0" y1="0" y2="1">
						<stop offset="0%" stopColor={color} stopOpacity="0.95" />
						<stop offset="100%" stopColor={color} stopOpacity="0.5" />
					</linearGradient>
				</defs>
				{data.map((v, i) => {
					const bh = (v / max) * (h - padding * 2);
					const x = padding + i * barWidth + 6;
					const y = h - padding - bh;
					return (
						<g key={i}>
							<rect x={x} y={y} width={barWidth - 12} height={bh} rx="6" fill="url(#barGradient)" />
						</g>
					);
				})}
				{/* baseline */}
				<line x1={padding} x2={w - padding} y1={h - padding} y2={h - padding} stroke="#E5E7EB" />
			</svg>
		</div>
	);
}


