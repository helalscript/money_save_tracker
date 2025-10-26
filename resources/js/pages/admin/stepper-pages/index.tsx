import { Alert, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { PlaceholderPattern } from '@/components/ui/placeholder-pattern';
import { Table, TableBody, TableCaption, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Pagination, PaginationContent, PaginationEllipsis, PaginationItem, PaginationLink, PaginationNext, PaginationPrevious } from "@/components/ui/pagination"
import AppLayout from '@/layouts/app-layout';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import { Card } from '@/components/ui/card';
import {
	InputGroup,
	InputGroupAddon,
	InputGroupButton,
	InputGroupInput,
	InputGroupText,
	InputGroupTextarea,
} from "@/components/ui/input-group"
import { Search } from 'lucide-react';
import { router } from '@inertiajs/react';

// Define the StepperPage interface
interface StepperPage {
	id: number;
	title: string;
	description: string | null;
	image: string | null;
	order_no: number;
	status: string;
}
// Define the Props interface
interface Props {
	pages: {
		data: StepperPage[];
		links: { url: string | null; label: string; active: boolean }[];
		current_page: number;
		last_page: number;
		total: number;
		prev_page_url: string | null;
		next_page_url: string | null;
	};
	filters: { search?: string; per_page?: number };
}
// Define the breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
	{ title: 'Dashboard', href: dashboard().url },
	{ title: 'Stepper Pages', href: '/admin/stepper-pages' },
];

const toggleStatus = (id: number, currentStatus: string) => {
	router.patch(`/admin/stepper-pages/${id}/toggle`, {
		status: currentStatus === 'active' ? 'inactive' : 'active',
	}, {
		preserveScroll: true,
		preserveState: true,
	});
};

const handleDelete = (id: number) => {
	if (confirm("Are you sure you want to delete this stepper page?")) {
		router.delete(`/admin/stepper-pages/${id}`, {
			preserveScroll: true,
			preserveState: true,
		});
	}
};

export default function Index({ pages, filters }: Props) {
	console.log(pages);
	return (
		<AppLayout breadcrumbs={breadcrumbs}>
			<Head title="Stepper Pages" />


			<Card className="m-4">

				<div className="flex items-center justify-between m-4">
					<div className="w-1/4">
						<InputGroup>
							<InputGroupInput placeholder="Search..." />
							<InputGroupAddon>
								<Search />
							</InputGroupAddon>
						</InputGroup>
					</div>
					<div className="w-1/2 text-right">
						<Link ><Button>Add New</Button></Link>
					</div>
				</div>
				<div className='mx-4'>
					{/* <pre>{JSON.stringify(pages, null, 2)}</pre> */}
					<Table className="w-full">

						<TableHeader className="variant-secondary bg-gray-100 dark:bg-gray-800 m-4">
							<TableRow>
								<TableHead className="w-[50px]">#</TableHead>
								<TableHead>Title</TableHead>
								<TableHead>Image</TableHead>
								<TableHead>Order No</TableHead>
								<TableHead>Status</TableHead>
								<TableHead>Action</TableHead>
							</TableRow>
						</TableHeader>

						<TableBody>
							{pages.data.length > 0 ? (
								pages.data.map((page: StepperPage, index: number) => (
									<TableRow key={page?.id}>
										<TableCell>{index + 1}</TableCell>
										<TableCell>{page?.title}</TableCell>
										{/* ✅ Image */}
										<TableCell>
											{page?.image ? (
												<img
													src={`/${page?.image}`}
													alt={page?.title}
													className="w-16 h-16 object-cover rounded"
												/>
											) : (
												<span className="text-gray-400">No Image</span>
											)}
										</TableCell>
										<TableCell>{page?.order_no}</TableCell>
										{/* ✅ Status Toggle Button */}
										<TableCell>
											<button
												onClick={() => toggleStatus(page?.id, page?.status)}
												className={`px-3 py-1 rounded-full text-white text-sm ${page?.status === "active" ? "bg-green-500" : "bg-red-500"
													}`}
											>
												{page.status === "active" ? "Active" : "Inactive"}
											</button>
										</TableCell>

										{/* ✅ Action Buttons */}
										<TableCell className="flex gap-2">
											<Link href={`/admin/stepper-pages/${page?.id}/edit`}>
												<Button size="sm" variant="outline">Edit</Button>
											</Link>

											<Button
												size="sm"
												variant="destructive"
												onClick={() => handleDelete(page?.id)}
											>
												Delete
											</Button>
										</TableCell>
									</TableRow>
								))
							) : (
								<TableRow>
									<TableCell colSpan={5} className="text-center">
										No Data Available
									</TableCell>
								</TableRow>
							)}
						</TableBody>
					</Table>
					{/* Pagination Controls */}
					<div className="flex items-end justify-end m-4">
						<Pagination className="flex-1">
							<PaginationContent>
								{/* Page Numbers */}
								{pages.links.map((link, index) => (
									<PaginationItem key={index}>
										{link.url ? (
											<PaginationLink
												href={link.url}
												isActive={link.active}
												dangerouslySetInnerHTML={{ __html: link.label }}
											/>
										) : (
											<PaginationEllipsis />
										)}
									</PaginationItem>
								))}
							</PaginationContent>
						</Pagination>
					</div>
				</div>
			</Card>

		</AppLayout>
	);
}