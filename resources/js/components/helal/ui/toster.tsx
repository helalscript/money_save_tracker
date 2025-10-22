import React, { useState } from "react";
import { cn } from "@/lib/utils";

export interface TosterProps {
    image?: string;
    name?: string;
    onClose?: () => void;
    className?: string;
    children?: React.ReactNode;
}

const Toster: React.FC<TosterProps> = ({
    image,
    name,
    onClose,
    className,
    children,
}) => {
    const [visible, setVisible] = useState(true);

    if (!visible) return null;

    return (
        <div
            className={cn(
                "flex items-center justify-between w-[460px] bg-white shadow-md rounded-2xl border border-gray-200 overflow-hidden",
                className
            )}
        >
            {/* Left side: image + content */}
            <div className="flex items-center gap-3 p-3">
                {image && (
                    <img
                        src={image}
                        alt={name}
                        className="w-10 h-10 rounded-full object-cover border"
                    />
                )}
                <div>
                    {name && (
                        <h4 className="font-semibold text-gray-900 text-sm">{name}</h4>
                    )}
                    <div className="text-gray-600 text-sm">{children}</div>
                </div>
            </div>

            {/* Right side: close button */}
            <button
                onClick={() => {
                    setVisible(false);
                    if (onClose) onClose();
                }}
                className="px-4 text-sm font-medium text-indigo-600 hover:text-indigo-800 border-l border-gray-200"
            >
                Close
            </button>
        </div>
    );
};

const TosterTitle: React.FC<
    React.HTMLAttributes<HTMLDivElement>
> = ({ className, children, ...props }) => (
    <div
        className={cn("font-semibold text-gray-900 text-sm", className)}
        {...props}
    >
        {children}
    </div>
);

const TosterDescription: React.FC<
    React.HTMLAttributes<HTMLDivElement>
> = ({ className, children, ...props }) => (
    <div
        className={cn("text-gray-600 text-sm", className)}
        {...props}
    >
        {children}
    </div>
);

export { Toster, TosterTitle, TosterDescription };