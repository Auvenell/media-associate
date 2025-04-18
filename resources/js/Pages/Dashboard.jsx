import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Head } from "@inertiajs/react";
import InboundGrid from "./InboundGrid";
import { useState } from "react";
import axios from "axios";

export default function Dashboard({ auth, inbounds = [] }) {
    return (
        <AuthenticatedLayout
            user={auth.user}
            header={
                <h2 className="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
                    Dashboard
                </h2>
            }
        >
            <Head title="Dashboard" />

            <div className="py-12 bg-gray-100 dark:bg-gray-900 min-h-screen text-gray-800 dark:text-gray-100">
                <div className="max-w-8xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="mt-6 px-5 py-4">
                            <h3 className="text-lg font-bold mb-4">
                                Your Inbounds
                            </h3>
                            <div className="grid gap-6 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4 bg-gray-50 dark:bg-gray-700 p-6 rounded-lg">
                                <InboundGrid inbounds={inbounds} />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
