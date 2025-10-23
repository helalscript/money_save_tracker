import React from 'react';
import { Link, usePage } from '@inertiajs/react';

export default function Show() {
	const { props }: any = usePage();
	const page = props.page;

	return (
		<div className="p-6 space-y-4 max-w-3xl">
			<div className="flex items-center justify-between">
				<h1 className="text-2xl font-semibold">{page.title}</h1>
				<div className="space-x-2">
					<Link className="btn btn-sm" href={route('admin.stepper-pages.edit', page.id)}>Edit</Link>
					<Link className="btn btn-sm btn-ghost" href={route('admin.stepper-pages.index')}>Back</Link>
				</div>
			</div>
			{page.image && (
				<img src={page.image} alt={page.title} className="rounded border max-h-64" />
			)}
			<p className="opacity-80 whitespace-pre-wrap">{page.description}</p>
			<div className="text-sm">Order: {page.order_no} • Status: {page.status}</div>
		</div>
	);
}
