import React from 'react';

export default function StatsCard({ label, value, change, intent = 'green' }) {
	return (
		<div className={`admin-stat-card ${intent}`}>
			<div className="admin-stat-label">{label}</div>
			<div className="admin-stat-value">{value}</div>
			<div className="admin-stat-change">{change}</div>
		</div>
	);
}


