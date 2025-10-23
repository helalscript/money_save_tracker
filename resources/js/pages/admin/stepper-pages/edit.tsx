import React from 'react';
import { Link, useForm, usePage } from '@inertiajs/react';

interface StepperPage { id:number; title:string; description:string|null; image:string|null; order_no:number; status:string }

export default function Edit() {
	const { props }: any = usePage();
	const page: StepperPage = props.page;
	const { data, setData, put, processing } = useForm({
		title: page.title || '',
		description: page.description || '',
		image: page.image || '',
		order_no: page.order_no || 0,
		status: page.status || 'active',
	});

	function submit(e: React.FormEvent) {
		e.preventDefault();
		put(route('admin.stepper-pages.update', page.id));
	}

	return (
		<div className="p-6 space-y-4">
			<h1 className="text-2xl font-semibold">Edit Stepper Page</h1>
			<form onSubmit={submit} className="space-y-3 max-w-xl">
				<input className="input input-bordered w-full" placeholder="Title" value={data.title} onChange={e=>setData('title', e.target.value)} />
				<textarea className="textarea textarea-bordered w-full" placeholder="Description" value={data.description} onChange={e=>setData('description', e.target.value)} />
				<input className="input input-bordered w-full" placeholder="Image URL" value={data.image} onChange={e=>setData('image', e.target.value)} />
				<input type="number" className="input input-bordered w-full" placeholder="Order" value={data.order_no} onChange={e=>setData('order_no', Number(e.target.value))} />
				<select className="select select-bordered w-full" value={data.status} onChange={e=>setData('status', e.target.value)}>
					<option value="active">active</option>
					<option value="inactive">inactive</option>
				</select>
				<button className="btn" disabled={processing}>Update</button>
				<Link href={route('admin.stepper-pages.index')} className="btn btn-ghost">Cancel</Link>
			</form>
		</div>
	);
}
