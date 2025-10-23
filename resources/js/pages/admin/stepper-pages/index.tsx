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


interface StepperPage {
	id: number;
	title: string;
	description: string | null;
	image: string | null;
	order_no: number;
	status: string;
}

interface Props {
	pages: {
		data: StepperPage[];
		links: { url: string | null; label: string; active: boolean }[];
		current_page: number;
		last_page: number;
		total: number;
	};
	filters: { search?: string; per_page?: number };
}
const breadcrumbs: BreadcrumbItem[] = [
	{ title: 'Dashboard', href: '/dashboard' },
	{ title: 'Stepper Pages', href: '/admin/stepper-pages' },
];



export default function Index({ pages, filters }: Props) {
console.log(pages.data);
	return (
		<AppLayout breadcrumbs={breadcrumbs}>
			<Head title="Stepper Pages" />

			<div className="m-4">
				<Link ><Button>Add New</Button></Link>
			</div>
			<div className="m-4">
				{/* <pre>{JSON.stringify(pages, null, 2)}</pre> */}
				<Table>
					<TableCaption>A list of your recent invoices.</TableCaption>
					<TableHeader>
						<TableRow>
							<TableHead className="w-[100px]">Invoice</TableHead>
							<TableHead>Status</TableHead>
							<TableHead>Method</TableHead>
							<TableHead className="text-right">Amount</TableHead>
						</TableRow>
					</TableHeader>
					<TableBody>
						<TableRow>
							<TableCell className="font-medium">INV001</TableCell>
							<TableCell>Paid</TableCell>
							<TableCell>Credit Card</TableCell>
							<TableCell className="text-right">$250.00</TableCell>
						</TableRow>
					</TableBody>
				</Table>
				<Pagination>
					<PaginationContent>
						<PaginationItem>
							<PaginationPrevious href="#" />
						</PaginationItem>
						<PaginationItem>
							<PaginationLink href="#">1</PaginationLink>
						</PaginationItem>
						<PaginationItem>
							<PaginationEllipsis />
						</PaginationItem>
						<PaginationItem>
							<PaginationNext href="#" />
						</PaginationItem>
					</PaginationContent>
				</Pagination>
			</div>
		</AppLayout>
	);
}