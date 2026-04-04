package com.sieuthithucung.mapper;

import com.sieuthithucung.dto.PaymentDto;
import com.sieuthithucung.entity.PaymentEntity;
import org.springframework.stereotype.Component;
import tools.jackson.databind.ObjectMapper;

@Component
public class PaymentMapper extends BaseMapper<PaymentEntity, PaymentDto> {

    public PaymentMapper(ObjectMapper objectMapper) {
        super(objectMapper, PaymentEntity.class, PaymentDto.class);
    }
}

