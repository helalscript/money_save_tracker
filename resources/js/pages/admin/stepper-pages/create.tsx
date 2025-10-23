import React from 'react';
import { Link, useForm } from '@inertiajs/react';

export default function Create() {
	const { data, setData, post, processing, errors } = useForm({
		title: '',
		description: '',
		image: '',
		order_no: 0,
		status: 'active',
	});

	function submit(e: React.FormEvent) {
		e.preventDefault();
		post(route('admin.stepper-pages.store'));
	}

	return (
		<div className="p-6 space-y-4">
			<h1 className="text-2xl font-semibold">Create Stepper Page</h1>
			<form onSubmit={submit} className="space-y-3 max-w-xl">
				<input className="input input-bordered w-full" placeholder="Title" value={data.title} onChange={e=>setData('title', e.target.value)} />
				<textarea className="textarea textarea-bordered w-full" placeholder="Description" value={data.description} onChange={e=>setData('description', e.target.value)} />
				<input className="input input-bordered w-full" placeholder="Image URL" value={data.image} onChange={e=>setData('image', e.target.value)} />
				<input type="number" className="input input-bordered w-full" placeholder="Order" value={data.order_no} onChange={e=>setData('order_no', Number(e.target.value))} />
				<select className="select select-bordered w-full" value={data.status} onChange={e=>setData('status', e.target.value)}>
					<option value="active">active</option>
					<option value="inactive">inactive</option>
				</select>
				<button className="btn" disabled={processing}>Save</button>
				<Link href={route('admin.stepper-pages.index')} className="btn btn-ghost">Cancel</Link>
			</form>
		</div>
	);
}
