export default function AppLogo() {
    return (
        <>
            <div className="flex aspect-square size-8 items-center justify-center">
                <img
                    src="/Logo-white.png"
                    alt="ManagerClin"
                    className="size-8 rounded-md object-contain dark:hidden"
                />
                <img
                    src="/Logo-dark.png"
                    alt="ManagerClin"
                    className="size-8 rounded-md object-contain hidden dark:block"
                />
            </div>
            <div className="ml-1 grid flex-1 text-left text-sm">
                <span className="mb-0.5 truncate leading-tight font-semibold">ManagerClin</span>
            </div>
        </>
    );
}
