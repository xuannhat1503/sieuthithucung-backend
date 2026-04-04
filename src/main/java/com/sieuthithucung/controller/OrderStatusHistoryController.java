package com.sieuthithucung.controller;

import com.sieuthithucung.common.AbstractCrudController;
import com.sieuthithucung.dto.OrderStatusHistoryDto;
import com.sieuthithucung.service.OrderStatusHistoryService;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RestController;

@RestController
@RequestMapping("/api/v1/order-status-history")
public class OrderStatusHistoryController extends AbstractCrudController<Long, OrderStatusHistoryDto> {

    public OrderStatusHistoryController(OrderStatusHistoryService service) {
        super(service);
    }
}

