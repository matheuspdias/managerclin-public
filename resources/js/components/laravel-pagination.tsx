import {
    Pagination,
    PaginationContent,
    PaginationEllipsis,
    PaginationItem,
    PaginationLink,
    PaginationNext,
    PaginationPrevious,
} from '@/components/ui/pagination';

interface LaravelPaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface LaravelPaginationProps {
    links: LaravelPaginationLink[];
    onPageChange: (page: number) => void;
}

function extractPageNumberFromUrl(url: string | null): number {
    if (!url) return 1;
    const match = url.match(/page=(\d+)/);
    return match ? parseInt(match[1], 10) : 1;
}

export default function LaravelPagination({ links, onPageChange }: LaravelPaginationProps) {
    if (!links.length) return null;

    const currentPageIndex = links.findIndex((link) => link.active);
    const totalPages = links.length;

    const visibleLinks = links.filter((_, index) => {
        return (
            index === 0 || // Previous
            index === totalPages - 1 || // Next
            index === 1 || // First page
            index === totalPages - 2 || // Last page
            Math.abs(index - currentPageIndex) <= 2
        );
    });

    return (
        <Pagination className="mt-4">
            <PaginationContent>
                {visibleLinks.map((link, index) => {
                    const isPrevious = link.label.toLowerCase().includes('previous');
                    const isNext = link.label.toLowerCase().includes('next');
                    const isEllipsis = link.label === '...';
                    const pageNumber = extractPageNumberFromUrl(link.url);

                    if (isEllipsis) {
                        return (
                            <PaginationItem key={index}>
                                <PaginationEllipsis />
                            </PaginationItem>
                        );
                    }

                    if (isPrevious) {
                        return (
                            <PaginationItem key={index}>
                                <PaginationPrevious onClick={() => onPageChange(pageNumber)} />
                            </PaginationItem>
                        );
                    }

                    if (isNext) {
                        return (
                            <PaginationItem key={index}>
                                <PaginationNext onClick={() => onPageChange(pageNumber)} />
                            </PaginationItem>
                        );
                    }

                    return (
                        <PaginationItem key={index}>
                            <PaginationLink
                                isActive={link.active}
                                onClick={() => onPageChange(pageNumber)}
                                dangerouslySetInnerHTML={{ __html: link.label }}
                            />
                        </PaginationItem>
                    );
                })}
            </PaginationContent>
        </Pagination>
    );
}
