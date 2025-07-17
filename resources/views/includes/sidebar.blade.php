<div class="col-lg-3 col-12 sidebar-container">
                    <div class="customer-sidebar pb-3 flex-column gap-4" id="customer-sidebar">
                        <span class="fa fa-arrow-right closeSidebar rounded-3" onclick="_toggle_customer_sidebar()"></span>
                        <div class="user-info px-3 mt-md-5 mt-3">
                            <img width="75px" height="75px" class="rounded-circle" src="{{asset('uploads/images/user.png')}}" alt="user">
                            <div class="fw-bold h5 mt-3">{{ auth()->user()->name }}</div>
                            <div class="fs-14 text-muted">{{ auth()->user()->email }}</div>
                        </div>
                        <ul class="navbar-nav p-0 w-100">
                        @if(auth()->user()->role === 'super_manager')
                            <li class="nav-item mb-1">
                                <a href="{{ route('admin.home') }}" class="p-2 pe-3 w-100 rounded-pill d-flex align-items-center underline-0 text-dark {{ Route::is('admin.home') ? 'active' : '' }}">
                                    <span class="fa fa-dashboard ms-3" style="min-width:15px;"></span>
                                    لوحة التحكم
                                </a>
                            </li>
                            <li class="nav-item mb-1">
                                <a href="{{ route('admin.departments.index') }}" class="p-2 pe-3 w-100 rounded-pill d-flex align-items-center underline-0 text-dark {{ Route::is('admin.departments.index') ? 'active' : '' }}">
                                    <span class="fa fa-building ms-3" style="min-width:15px;"></span>
                                    الأقسام
                                </a>
                            </li>
                            <li class="nav-item mb-1">
                                <a href="{{ route('admin.standards.index') }}" class="p-2 pe-3 w-100 rounded-pill d-flex align-items-center underline-0 text-dark {{ Route::is('admin.standards.index') ? 'active' : '' }}">
                                    <span class="fa fa-list ms-3" style="min-width:15px;"></span>
                                    المعايير
                                </a>
                            </li>
                            <li class="nav-item mb-1">
                                <a href="{{ route('admin.managers.index') }}" class="p-2 pe-3 w-100 rounded-pill d-flex align-items-center underline-0 text-dark {{ Route::is('admin.managers.index') ? 'active' : '' }}">
                                    <span class="fa fa-user-shield ms-3" style="min-width:15px;"></span>
                                    المدراء
                                </a>
                            </li>
                            <li class="nav-item mb-1">
                                <a href="{{ route('admin.departments-managers.index') }}" class="p-2 pe-3 w-100 rounded-pill d-flex align-items-center underline-0 text-dark {{ Route::is('admin.departments-managers.index') ? 'active' : '' }}">
                                    <span class="fa fa-users ms-3" style="min-width:15px;"></span>
                                    مدراء الأقسام/الموظفيين
                                </a>
                            </li>
                           
                        @endif
                        @if(auth()->user()->role === 'manager')
                            <li class="nav-item mb-1">
                                <a href="{{ route('manager.evaluation.index') }}" class="p-2 pe-3 w-100 rounded-pill d-flex align-items-center underline-0 text-dark {{ Route::is('manager.evaluation.index') ? 'active' : '' }}">
                                    <span class="fa fa-users ms-3" style="min-width:15px;"></span>
                                    رؤساء الأقسام/الموظفيين
                                </a>
                            </li>
                            <li class="nav-item mb-1">
                                <a href="{{ route('manager.departments.index') }}" class="p-2 pe-3 w-100 rounded-pill d-flex align-items-center underline-0 text-dark {{ Route::is('manager.departments.index') ? 'active' : '' }}">
                                <span class="fa fa-building ms-3" style="min-width:15px;"></span>
تقييمات أقسام الشركة
                                </a>
                            </li>
                            
                        @endif
                        @if(auth()->user()->role === 'department_manager')
                            <li class="nav-item mb-1">
                                <a href="{{ route('department_manager.evaluation.index') }}" class="p-2 pe-3 w-100 rounded-pill d-flex align-items-center underline-0 text-dark {{ Route::is('department_manager.evaluation.index') ? 'active' : '' }}">
                                    <span class="fa fa-dashboard ms-3" style="min-width:15px;"></span>
                                    تقييم الموظفين
                                </a>
                            </li>
                            <li class="nav-item mb-1">
                                <a href="{{ route('department_manager.evaluation.showEvaluation') }}" class="p-2 pe-3 w-100 rounded-pill d-flex align-items-center underline-0 text-dark {{ Route::is('department_manager.evaluation.showEvaluation') ? 'active' : '' }}">
                                    <span class="fa fa-dashboard ms-3" style="min-width:15px;"></span>
                                    التقييم الشخصي
                                </a>
                            </li>
                        @endif
                        @if(auth()->user()->role === 'employee')
                        <li class="nav-item mb-1">
                                <a href="{{ route('employee.evaluation.showEvaluation') }}" class="p-2 pe-3 w-100 rounded-pill d-flex align-items-center underline-0 text-dark {{ Route::is('employee.evaluation.showEvaluation') ? 'active' : '' }}">
                                    <span class="fa fa-dashboard ms-3" style="min-width:15px;"></span>
                                    التقييم الشخصي
                                </a>
                            </li>
                        @endif
                        @auth
                            <li class="nav-item mb-1">
                                <a href="{{ route('missions.index') }}" class="p-2 pe-3 w-100 rounded-pill d-flex align-items-center underline-0 text-dark {{ Route::is('missions.index') ? 'active' : '' }}">
                                    <span class="fa fa-dashboard ms-3" style="min-width:15px;"></span>
                                    المهام
                                </a>
                            </li>
                            <li class="nav-item mb-1">
                                <a href="{{ route('profile.index') }}" class="p-2 pe-3 w-100 rounded-pill d-flex align-items-center underline-0 text-dark {{ Route::is('profile.index') ? 'active' : '' }}">
                                    <span class="fa fa-user ms-3" style="min-width:15px;"></span>
                                    الملف الشخصي
                                </a>
                            </li>
                        @endauth
                        </ul>
                    </div>
                </div>